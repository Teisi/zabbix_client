CREATE TABLE tx_zabbixclient_domain_model_lock (
    ip varchar(50) DEFAULT '' NOT NULL,
    count int(11) DEFAULT '0' NOT NULL,
);

CREATE TABLE tx_zabbixclient_domain_model_felog (
    uid int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    pid int(11) NOT NULL DEFAULT 0,
    tstamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    error tinyint(4) unsigned DEFAULT '0' NOT NULL,
    details text,
    log_data JSON,
    log_message text,
    PRIMARY KEY (uid)
);
