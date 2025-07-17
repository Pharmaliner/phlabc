CREATE TABLE tx_phlabc_domain_model_role (
    uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
    pid int(11) DEFAULT 0 NOT NULL,
    permissions int(11) DEFAULT 0 NOT NULL,

    is_custom_role tinyint(4) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,

    tstamp int(11) unsigned DEFAULT 0 NOT NULL,
    crdate int(11) unsigned DEFAULT 0 NOT NULL,
    deleted tinyint(4) unsigned DEFAULT 0 NOT NULL,
    hidden tinyint(4) unsigned DEFAULT 0 NOT NULL,
    sys_language_uid int(11) DEFAULT 0 NOT NULL,
    l18n_parent int(11) DEFAULT 0 NOT NULL,
    l18n_diffsource mediumblob,
    fe_group int(11) DEFAULT 0 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
);

CREATE TABLE tx_phlabc_domain_model_permission (
    uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
    pid int(11) DEFAULT 0 NOT NULL,
    roles int(11) DEFAULT 0 NOT NULL,

    permission_key VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,

    tstamp int(11) unsigned DEFAULT 0 NOT NULL,
    crdate int(11) unsigned DEFAULT 0 NOT NULL,
    deleted tinyint(4) unsigned DEFAULT 0 NOT NULL,
    hidden tinyint(4) unsigned DEFAULT 0 NOT NULL,
    sys_language_uid int(11) DEFAULT 0 NOT NULL,
    l18n_parent int(11) DEFAULT 0 NOT NULL,
    l18n_diffsource mediumblob,
    fe_group int(11) DEFAULT 0 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
);

CREATE TABLE tx_phlabc_role_permission_mm (
    uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
    pid int(11) DEFAULT 0 NOT NULL,

    uid_local int(10) unsigned NOT NULL,
    uid_foreign int(10) unsigned NOT NULL,
    sorting int(10) unsigned NOT NULL,
    sorting_foreign int(10) unsigned NOT NULL,

    tstamp int(10) unsigned NOT NULL,
    crdate int(10) unsigned NOT NULL,
    hidden tinyint(3) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE fe_users (
    roles int(11) DEFAULT 0 NOT NULL,
);

CREATE TABLE tx_phlabc_role_user_mm (
    uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
    pid int(11) DEFAULT 0 NOT NULL,

    uid_local int(10) unsigned NOT NULL,
    uid_foreign int(10) unsigned NOT NULL,
    sorting int(10) unsigned NOT NULL,
    sorting_foreign int(10) unsigned NOT NULL,

    tstamp int(10) unsigned NOT NULL,
    crdate int(10) unsigned NOT NULL,
    hidden tinyint(3) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE fe_groups (
    roles int(11) NOT NULL,
);

CREATE TABLE tx_phlabc_role_group_mm (
    uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
    pid int(11) DEFAULT 0 NOT NULL,

    uid_local int(10) unsigned NOT NULL,
    uid_foreign int(10) unsigned NOT NULL,
    sorting int(10) unsigned NOT NULL,
    sorting_foreign int(10) unsigned NOT NULL,

    tstamp int(10) unsigned NOT NULL,
    crdate int(10) unsigned NOT NULL,
    hidden tinyint(3) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);