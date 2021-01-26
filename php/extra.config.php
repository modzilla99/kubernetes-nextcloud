<?php
$CONFIG = array (
  /**
   * This parameter determines where the Nextcloud logs are sent.
   * ``file``: the logs are written to file ``nextcloud.log`` in the default
   * Nextcloud data directory.
   * ``syslog``: the logs are sent to the system log. This requires a syslog daemon
   * to be active.
   * ``errorlog``: the logs are sent to the PHP ``error_log`` function.
   * ``systemd``: the logs are sent to the Systemd journal. This requires a system
   * that runs Systemd and the Systemd journal. The PHP extension ``systemd``
   * must be installed and active.
   *
   * Defaults to ``file``
   */
  'log_type' => 'file',

  /**
   * Name of the file to which the Nextcloud logs are written if parameter
   * ``log_type`` is set to ``file``.
   *
   * Defaults to ``[datadirectory]/nextcloud.log``
   */
  'logfile' => '/var/log/nextcloud.log',

  /**
   * Log file mode for the Nextcloud loggin type in octal notation.
   *
   * Defaults to 0640 (writeable by user, readable by group).
   */
  'logfilemode' => 0640,

  /**
   * Loglevel to start logging at. Valid values are: 0 = Debug, 1 = Info, 2 =
   * Warning, 3 = Error, and 4 = Fatal. The default value is Warning.
   *
   * Defaults to ``2``
   */
  'loglevel' => 3,

  /**
   * The timezone for logfiles. You may change this; see
   * https://www.php.net/manual/en/timezones.php
   *
   * Defaults to ``UTC``
   */
  'logtimezone' => 'Europe/Berlin',
  
  /**
   * This sets the default language on your Nextcloud server, using ISO_639-1
   * language codes such as ``en`` for English, ``de`` for German, and ``fr`` for
   * French. It overrides automatic language detection on public pages like login
   * or shared items. User's language preferences configured under "personal ->
   * language" override this setting after they have logged in. Nextcloud has two
   * distinguished language codes for German, 'de' and 'de_DE'. 'de' is used for
   * informal German and 'de_DE' for formal German. By setting this value to 'de_DE'
   * you can enforce the formal version of German unless the user has chosen
   * something different explicitly.
   *
   * Defaults to ``en``
   */
  'default_language' => 'de',

  /**
   * This sets the default locale on your Nextcloud server, using ISO_639
   * language codes such as ``en`` for English, ``de`` for German, and ``fr`` for
   * French, and ISO-3166 country codes such as ``GB``, ``US``, ``CA``, as defined
   * in RFC 5646. It overrides automatic locale detection on public pages like
   * login or shared items. User's locale preferences configured under "personal
   * -> locale" override this setting after they have logged in.
   *
   * Defaults to ``en``
   */
  'default_locale' => 'de',

  /**
   * This sets the default region for phone numbers on your Nextcloud server,
   * using ISO 3166-1 country codes such as ``DE`` for Germany, ``FR`` for France, …
   * It is required to allow inserting phone numbers in the user profiles starting
   * without the country code (e.g. +49 for Germany).
   *
   * No default value!
   */
  'default_phone_region' => 'DE',

  /**
   * Set the default app to open on login. Use the app names as they appear in the
   * URL after clicking them in the Apps menu, such as documents, calendar, and
   * gallery. You can use a comma-separated list of app names, so if the first
   * app is not enabled for a user then Nextcloud will try the second one, and so
   * on. If no enabled apps are found it defaults to the dashboard app.
   *
   * Defaults to ``dashboard,files``
   */
  'defaultapp' => 'files',

  /**
   * Specifies how often the local filesystem (the Nextcloud data/ directory, and
   * NFS mounts in data/) is checked for changes made outside Nextcloud. This
   * does not apply to external storages.
   *
   * 0 -> Never check the filesystem for outside changes, provides a performance
   * increase when it's certain that no changes are made directly to the
   * filesystem
   *
   * 1 -> Check each file or folder at most once per request, recommended for
   * general use if outside changes might happen.
   *
   * Defaults to ``0``
   */
  'filesystem_check_changes' => 0,

  /**
   * Secret used by Nextcloud for various purposes, e.g. to encrypt data. If you
   * lose this string there will be data corruption.
   */
  'secret' => '',
);

