CREATE TABLE `__TABLE_NAME__` (
  `itm_idx` bigint(20) NOT NULL COMMENT '생산제품idx',
  `com_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT '업체번호',
  `imp_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT 'IMPidx',
  `mms_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT 'MMSidx',
  `mmg_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT '설비그룹idx',
  `shf_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT '작업구간idx',
  `itm_shift` int(11) NOT NULL DEFAULT 0 COMMENT '구간번호',
  `bom_idx` bigint(20) NOT NULL DEFAULT 0 COMMENT 'BOMidx',
  `bom_part_no` varchar(100) DEFAULT '' COMMENT '파트넘버',
  `itm_price` int(11) NOT NULL DEFAULT 0 COMMENT '단가',
  `itm_count` int(11) NOT NULL DEFAULT 0 COMMENT '수량',
  `itm_defect` int(11) NOT NULL DEFAULT 0 COMMENT '불량여부',
  `itm_defect_type` int(11) NOT NULL DEFAULT 0 COMMENT '불량타입',
  `itm_date` date DEFAULT '0000-00-00' COMMENT '날짜'
  PRIMARY KEY (`itm_idx`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;