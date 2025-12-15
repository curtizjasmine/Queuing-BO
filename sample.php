<?php
echo password_hash("123", PASSWORD_DEFAULT);
?>

INSERT INTO tbl_users
(username, password, role, full_name, status, department_id, schedule_date, schedule_time)
VALUES
('doctor1', '$2y$10$NQtblwLNYAmng84CKmZSg.vcOIyyJPFcpxW.wA8ygVq3RfWyUeHEC', 'teller', 'staff_one', 'active', 1, 'N/A', 'N/A');
