<?php
class PatientQuery {

    // Get pending patients
public static function getPendingPatients($conn, $status1, $status2) {
    try {
 
              if ($_SESSION['jobassign'] == "Triage") {
               $sql = "SELECT *
FROM tbl_checklist c
INNER JOIN tbl_patients p ON c.checklist_id = p.checklist_id
WHERE c.triage = ''
  AND p.status_patients IN ('Pending', 'Serving'); ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if no results found
        if (empty($result)) {
            return "No pending patients found.";
        } else {
            return $result;
        }
        
          }else  if ($_SESSION['jobassign'] == "Registration"){
                      $sql = "SELECT *
FROM tbl_checklist c
INNER JOIN tbl_patients p ON c.checklist_id = p.checklist_id
WHERE c.triage = 'Done'
  AND p.status_patients IN ('Pending', 'Serving'); ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if no results found
        if (empty($result)) {
            return "No pending patients found.";
        } else {
            return $result;
        }  
          }

    } catch (PDOException $e) {
        echo "Query Error: " . $e->getMessage();
        return false;
    }
}




public static function doupdatePatientss($conn, $id, $newStatus) {
    try {
        $sql = "UPDATE tbl_patients 
                SET status_patients = ?, 
                    user_id = ?, 
                    serveBy = ?, 
                    ticket_finished = DATE_FORMAT(NOW(), '%h:%i %p') 
                WHERE patient_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $newStatus,
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $id
        ]);

        return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
        echo "Query Error: " . $e->getMessage();
        return false;
    }
}
// public static function doupdatePatients($conn, $id, $fname, $lname, $mname, $age,$civil_Status, $contact_no, $home_address, $BP, $users_gender, $Comp_stat) {
public static function doupdatePatients($conn, $patient_id,$BP,$fname, $lname,$mname,$home_address, $users_gender) {
    
    try {
        $sql = "UPDATE tbl_patients 
                SET BP = ?,
                    firstname = ?
                    lastname = ?
                    middlename = ?
                    home_address = ?
                    gender = ?

                    ticket_finished = DATE_FORMAT(NOW(), '%h:%i %p') 
                WHERE patient_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->execute([$BP,$fname, $lname,$mname,$home_address, $users_gender,$patient_id,
             
            

            // $sym_fever, $has_cough, $has_sorethroat,
            // $has_shortnessBreath, $has_influenza_Symptoms,
            // $has_history_Covid, $have_localTransimission,
            // $have_contact_recentTravel, $has_inluenza_illness,
            // $has_contactConfirm, $existingConditions,
            // $admissionDate, $admitted_conditions, $historyICU,

            
        ]);
        

        return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
        echo "Query Error: " . $e->getMessage();
        return false;
    }


    // try {
    //     $sql = "UPDATE tbl_patients 
    //             SET status_patients = ?, 
    //                  user_id = ?, 
    //                  firstname = ?,
    //                  lastname = ?,
    //                  middlename = ?,
    //                  home_address = ?,
    //                  gender = ?,
    //                  BP = ?,
    //                  civil_status = ?,
    //                  contact_no = ?,
    //                  serveBy = ?, 
    //                 ticket_finished = DATE_FORMAT(NOW(), '%h:%i %p') 
    //             WHERE patient_id = ?";

    //     $stmt = $conn->prepare($sql);

    //     $stmt->execute([
    //        $Comp_stat, $_SESSION['user_id'],$fname, $lname, $mname,$home_address,$users_gender,$users_gender,$BP,$civil_Status,$contact_no, $_SESSION['user_id'],$id
             
            

    //         // $sym_fever, $has_cough, $has_sorethroat,
    //         // $has_shortnessBreath, $has_influenza_Symptoms,
    //         // $has_history_Covid, $have_localTransimission,
    //         // $have_contact_recentTravel, $has_inluenza_illness,
    //         // $has_contactConfirm, $existingConditions,
    //         // $admissionDate, $admitted_conditions, $historyICU,

            
    //     ]);
        

    //     return $stmt->rowCount() > 0;

    // } catch (PDOException $e) {
    //     echo "Query Error: " . $e->getMessage();
    //     return false;
    // }
}
public static function SkippedTickets($conn, $id, $remarks, $newStatus) {
    try {

        // CHECK 1: Ensure session is set
        if (!isset($_SESSION['user_id'])) {
            return "ERROR: Session variable user_id is NOT set.";
        }

        // CHECK 2: Validate required inputs
        if (empty($id)) {
            return "ERROR: patient_id is empty.";
        }
        if (empty($newStatus)) {
            return "ERROR: newStatus is empty.";
        }

        $sql = "UPDATE tbl_patients 
                SET status_patients = ?, 
                    user_id = ?, 
                    serveBy = ?, 
                    remarks = ?, 
                    ticket_finished = DATE_FORMAT(NOW(), '%h:%i %p') 
                WHERE patient_id = ?";

        $stmt = $conn->prepare($sql);

        // EXECUTE QUERY
        if (!$stmt->execute([
            $newStatus,
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $remarks,
            $id
        ])) {
            // If PDO fails to execute
            $errorInfo = $stmt->errorInfo();
            return "SQL EXECUTION ERROR: " . $errorInfo[2];
        }

        // CHECK IF UPDATE HAPPENED
        if ($stmt->rowCount() == 0) {
            return "NO UPDATE: Possible reasons → 
                - patient_id not found,
                - same data already stored,
                - status unchanged.";
        }

        return "SUCCESS";

    } catch (PDOException $e) {
        // LOG ERROR + RETURN MESSAGE
        error_log("SkippedTickets Error: " . $e->getMessage());
        return "PDO EXCEPTION: " . $e->getMessage();
    }
}


}
?>
