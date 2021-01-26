
IMAGE_NAME := "modzilla/nextcloud-nginx"
IMAGE_TAG := "latest"


build:
	docker pull docker.io/library/nginx:latest
	docker build -t "$(IMAGE_NAME):$(IMAGE_TAG)" .
push:
	docker push "$(IMAGE_NAME):$(IMAGE_TAG)"

