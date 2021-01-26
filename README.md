# Nextcloud on Kubernetes

These YAMLs can be used on a Kubernetes-cluster to set-up a Nextcloud instance using MariaDB, redis and nginx + fpm. Traefik v2 works as the ingress controller. This deployment was tested on k3os running k3s v1.18.9+k3s1 but should run on any other kubernetes distribution.

## Bugs
* You tell me

## Prerequisites:
* A Kubernetes cluster
* A storage class with RWX capabilties.
* A working Cert-Manager setup
* Basic Docker & Kubernetes knowledge

## Getting started

The first step is to clone the repo:
```bash
git clone https://github.com/modzilla/kubernetes-nextcloud/
```

### Step One

For the app to deploy you need to edit some files. Let's edit the certificate yaml first:

```yaml
#yamls/routing/certificates.yml
apiVersion: cert-manager.io/v1
kind: Certificate
metadata:
  name: nextcloud-cert
  namespace: nextcloud
spec:
  commonName: nextcloud.mydomain.com  #Put in your subdomain pointing to your server
  dnsNames:
  - nextcloud.mydomain.com            #Same here
  issuerRef:
    kind: ClusterIssuer
    name: letsencrypt-prod
  secretName: nextcloud-cert
---
apiVersion: cert-manager.io/v1
kind: Certificate
metadata:
  name: nextcloud-onlyoffice-cert
  namespace: nextcloud
spec:
  commonName: office.nextcloud.mydomain.com #This certificates is optional
  dnsNames:                                 #and only needed for onlyoffice
  - office.nextcloud.mydomain.com
  issuerRef:
    kind: ClusterIssuer
    name: letsencrypt-prod
  secretName: nextcloud-onlyoffice-cert
```

### Step Two
Now that we're set-up with certificates, a ingress ressource is needed for the traffic to be routed to the correct endpoint.
For that we edit yamls/routing/ingresses.yml to reflect the right address:
```yaml
#yamls/routing/ingresses.yml
apiVersion: traefik.containo.us/v1alpha1
kind: IngressRoute
metadata:
  name: nextcloud
  namespace: nextcloud
  labels:
    app: nextcloud
    tier: frontend
spec:
  entryPoints:
  - websecure
  routes:
  - kind: Rule
    match: Host(`nextcloud.mydomain.com`)       #your primary nextcloud address
    middlewares:
    - name: nextcloud-dav
      namespace: nextcloud
    - name: nextcloud-headers
      namespace: nextcloud
    services:
    - kind: Service
      name: nextcloud
      port: 80
  tls:
    secretName: nextcloud-cert
---
apiVersion: traefik.containo.us/v1alpha1
kind: IngressRoute
metadata:
  name: nextcloud-onlyoffice
  namespace: nextcloud
  labels:
    app: nextcloud
    tier: onlyoffice-backend
spec:
  entryPoints:
  - websecure
  routes:
  - kind: Rule
    match: Host(`office.nextcloud.mydomain.com`)    #Same as in Step One
    middlewares:
    - name: nextcloud-office
      namespace: nextcloud
    services:
    - kind: Service
      name: nextcloud-onlyoffice
      port: 80
  tls:
    secretName: nextcloud-onlyoffice-cert
```

### Step Three
Now we want to make sure that nextcloud accepts the right network for our reverse proxy (traefik).
For that we need to know what our container subnet is.
If you happen to use k3s, leave it as is and go to Step Four, otherwise follow along:

In my case I am using k3s with calico. The following command gives my two class-c networks.
```bash
#kubectl get nodes -o jsonpath='{.items[*].spec.podCIDR}'
10.10.0.0/24 10.10.1.0/24
```
This is because kubernetes automatically divides your big subnet into smaller chunks. Most kubernetes distributions use a /16 net, so this is what we are going with. In my case that would be 10.10.0.0/16, but ymmv. Google's your friend! 😉

Now that we know our cluster cidr, we need to insert it into the configmap.yml.
We can just run the following sed command that'll do that for us:
```bash
mv yamls/data/configmaps.yml configmaps.yml.backup # Let's make a backup of the file first, you never know
cat configmaps.yml.backup | \
    sed 's/10.42.0.0\/16/10.10.0.0\/16/g' | \
    tee yamls/data/configmaps.yml
```

### Setp Four

Let's configure the secrets!

```yaml
#yamls/secrets.yml
apiVersion: v1
kind: Secret
type: Opaque
metadata:
  name: nextcloud-mariadb-secret
  namespace: nextcloud
  labels:
    app: nextcloud
    tier: mariadb-backend
data:
  RootPassword: YjkyNmZmZWFkZDM1NDk5NjA1MGZkMjY4NWM5MjU5YzI=        #Needs to be changed
  DatabasePassword: ZmY4MTUxOTlhOTczOGYyNmVmZWVlMWUwZGU4ZjY1YzM=    #Needs to be changed
---
apiVersion: v1
kind: Secret
type: Opaque
metadata:
  name: nextcloud-redis-secret
  namespace: nextcloud
  labels:
    app: nextcloud
    tier: redis-backend
data:
  Password: YzY4YjRlMmE3NDU5N2U4NGFmNmQ5MmRlYTQ0YThhNTA=            #Needs to be changed
---
apiVersion: v1
kind: Secret
type: Opaque
metadata:
  name: nextcloud-secret
  namespace: nextcloud
  labels:
    app: nextcloud
    tier: fpm-backend
data:
  Domain: bmV4dGNsb3VkLm15ZG9tYWluLmNvbSxuZXh0Y2xvdWQubmV4dGNsb3VkLnN2Yy5jbHVzdGVyLmxvY2Fs
  DatabaseUser: bnh0Y2xkdXNlcg==
  DatabaseName: bmV4dGNsb3Vk                                        #Needs to be changed
---
apiVersion: v1
kind: Secret
type: Opaque
metadata:
  name: nextcloud-onlyoffice-secret
  namespace: nextcloud
  labels:
    app: nextcloud
    tier: onlyoffice-backend
data:
  secret: MDYwNzhmNjc5YzFlNTI1NjY3ZmUxMDliYzA1NzM1ZTg=      #Needs to be changed
  header: bmV4dGNsb3Vkb25seW9mZmljZWhlYWRlcg==

```

The entries marked as "needs to be changed" need unique passwords. To generate these, use the following command:

```bash
 openssl rand -hex 16 | tr -d '\n' | base64     #The truncate command is used to remove the newline after the generated password
```
To make the passwords longer change 16 to any supported value.

The following entry defines the trusted nextcloud domains:
```yaml
apiVersion: v1
kind: Secret
type: Opaque
metadata:
  name: nextcloud-secret
  namespace: nextcloud
  labels:
    app: nextcloud
    tier: fpm-backend
data:
  Domain: bmV4dGNsb3VkLm15ZG9tYWluLmNvbSxuZXh0Y2xvdWQubmV4dGNsb3VkLnN2Yy5jbHVzdGVyLmxvY2Fs
```

To change it for your setup, run:

```yaml
printf "nextcloud.mydomain.com,nextcloud.nextcloud.svc.cluster.local" | base64
```

The second domain is important for using cluster internal communication with onlyoffice.
(servicename.namespace.cluster-fqdn)


### Step Five

The configmap.yml still needs some love. It doesn't contain the right server address, passwords and email-credentials (optionl).
You need to edit overwrite.cli.url and jwt_secret (for onlyoffice).

To optain the jwt_secret from your generated secrets. Grep the secret entry of nextcloud-onlyoffice-secret and run:

```bash
echo "MDYwNzhmNjc5YzFlNTI1NjY3ZmUxMDliYzA1NzM1ZTg=" | base64 -d
```
Anything else should be self explainatory.

### Step Six

The last change you have to make is the storage size. Edit yamls/data/peristent-storage.yml for this.

## Final Step

Run the final deploy and hope for the best!

```bash
kubectl apply -f namespace.yml -f secrets.yml -f conjob.yml -f routing/ -f data/ -f deployments/
```

