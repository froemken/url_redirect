#
# Table structure for table 'tx_urlredirect_domain_model_config'
#
CREATE TABLE tx_urlredirect_domain_model_config (
  uid int(11) NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,

  use_reg_exp tinyint(1) unsigned DEFAULT '0' NOT NULL,
  domain int(11) unsigned DEFAULT '0' NOT NULL,
  request_uri text NOT NULL,
  target_uri text NOT NULL,
  http_status int(4) unsigned DEFAULT '0' NOT NULL,

  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid)
);
