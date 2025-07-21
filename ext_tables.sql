CREATE TABLE tx_phlabc_domain_model_role (
    uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
    pid int(11) DEFAULT 0 NOT NULL,

    is_custom_role tinyint(4) DEFAULT 0 NOT NULL,
    role_key VARCHAR(255) DEFAULT '' NOT NULL,
    title VARCHAR(255) DEFAULT '' NOT NULL,
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


CREATE TABLE fe_users (
    roles int(11) DEFAULT 0 NOT NULL,
);


CREATE TABLE fe_groups (
    roles int(11) NOT NULL,
);
