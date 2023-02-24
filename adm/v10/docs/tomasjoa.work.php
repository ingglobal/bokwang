SELECT 
		SQL_CALC_FOUND_ROWS * , 
		com.com_idx AS com_idx , 
		(SELECT prp_pay_date 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date , 
		(SELECT prp_price 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price , 
		(SELECT mb_hp 
				FROM g5_member 
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp , 
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name 
	FROM g5_1_project AS prj 
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx 
	WHERE prj_status = 'ok' AND prj_idx IN (171,167) ORDER BY prj_idx DESC LIMIT 0, 25
##############################################################################################################
SELECT 
		prj.prj_idx, 
		prj.prj_name,
		(SELECT prp_pay_date 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date , 
		(SELECT prp_price 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price , 
		(SELECT mb_hp 
				FROM g5_member 
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp , 
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name 
	FROM g5_1_project AS prj 
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx 
	WHERE prj_status = 'ok' 
		AND prj_idx IN (171,167) 
		AND
			(SELECT (SUM(IF(prp.prp_type = 'order' AND prp.prp_status = 'ok',prp.prp_price,0))
				  - SUM(IF(prp.prp_type NOT IN ('submit','nego','order','') AND prp.prp_pay_date != '0000-00-00' AND prp.prp_status = 'ok',prp.prp_price,0))) 
				  AS misu FROM g5_1_project_price AS prp WHERE prp.prj_idx = prj.prj_idx) > 0
		
	ORDER BY prj_idx DESC LIMIT 0, 25
##############################################################################################################
SELECT 
		prj.prj_idx, 
		prj.prj_name,
		(SELECT prp_pay_date 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date , 
		(SELECT prp_price 
				FROM g5_1_project_price 
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price , 
		(SELECT mb_hp 
				FROM g5_member 
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp , 
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name 
	FROM g5_1_project AS prj 
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx 
	WHERE prj_status = 'ok' 
		AND prj_idx IN (194,193) 
		AND
			((SELECT (SUM(IF(prp.prp_type = 'order' AND prp.prp_status = 'ok',prp.prp_price,0))
				  - SUM(IF(prp.prp_type NOT IN ('submit','nego','order','') AND prp.prp_pay_date != '0000-00-00' AND prp.prp_status = 'ok',prp.prp_price,0))) 
				  FROM g5_1_project_price AS prp WHERE prp.prj_idx = prj.prj_idx) > 0)
		
	ORDER BY prj_idx DESC LIMIT 0, 25