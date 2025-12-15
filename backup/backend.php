<?php
require '../database/db.php';
require '../auth/query.php';  

class login extends database {
    
    private $Pend_stat = "Pending";
    private $Serv_stat = "Serving";
    private $Comp_stat = "Completed";
    private $patient_id = '';
    private $fname = '';
    private $lname = '';
    private $mname = '';
    private $BOD = '';
    private $civil_Status = '';
    private $contact_no = '';
    private $home_address = '';
    private $BP = '';
    private $pressure = '';
    private $Weight = '';
    private $sym_fever = '';
    private $has_cough = '';
    private $has_sorethroat = '';
    private $has_shortnessBreath = '';
    private $has_influenza_Symptoms = '';
    private $has_history_Covid = '';
    private $have_localTransimission = '';
    private $have_contact_recentTravel = '';
    private $has_inluenza_illness = '';
    private $has_contactConfirm = '';
    private  $users_gender = '';
    private $medicine = '';
    private $existingConditions = '';
    private  $admissionDate = '';
    private $admitted_conditions = '';
    private $historyICU = '';
    private $took_antipyretics = '';
    private $remarks = '';
    private $status = '';
    
    public function getpendingTickets() {
        return $this->doDisplaypendingTickets();
    }

    public function doupdateStatusTickets($id) {
        $this->patient_id = $id;
        return $this->hasUpdated();
    }
        public function completedTickets($id,$BP,  $fname, 
            $lname, 
            $mname, 
           $user_gender,
            $home_address){
    
           $this->BP = $BP;
    // public function completedTickets($id,$fname,$lname,$mname,$BOD,$age,$civil_Status,$contact_no,$home_address,$user_gender,$BP){
          $this->patient_id = $id;
          $this->fname = $fname;
          $this->lname = $lname;
           $this->mname = $mname;
    //        $this->BOD = $BOD;
    //        $this->age = $age; 
    //        $this->civil_Status = $civil_Status;
    //        $this->contact_no = $contact_no;
           $this->home_address = $home_address;
    //        $this->BP = $BP;
        //    $this->pressure = $pressure;
        //    $this->Weight = $Weight;
        //    $this->sym_fever = $sym_fever;
        //    $this->has_cough = $has_cough;
        //    $this->has_sorethroat = $has_sorethroat;
        //    $this->has_shortnessBreath = $has_shortnessBreath;
        //    $this->has_influenza_Symptoms = $has_influenza_Symptoms;
        //    $this->has_history_Covid = $has_history_Covid;
        //    $this->have_localTransimission = $have_localTransimission;
        //    $this->have_contact_recentTravel = $have_contact_recentTravel;
        //    $this->has_inluenza_illness  = $has_inluenza_illness;
        //    $this->has_contactConfirm = $has_contactConfirm;
           $this->users_gender = $user_gender;
        //    $this->medicine = $medicine;
        //    $this->existingConditions = $existingConditions;
        //    $this->admissionDate = $admissionDate;
        //    $this->admitted_conditions = $admitted_conditions;
        //    $this->historyICU = $historyICU;

        return $this->hasCompleted();
    }
    public function SkippedTickets($id,$remarks,$stat){
        $this->patient_id = $id; 
        $this->remarks = $remarks;
        $this->status = $stat;
        return $this->hasDoSkipped();
    }
     private function hasCompleted(){
        $conn = $this->connect();

//         $updated = PatientQuery::doupdatePatients($conn, $this->patient_id,$this->fname,$this->lname, $this->mname,
//    $this->age,$this->civil_Status,$this->contact_no,$this->home_address,$this->BP,$this->users_gender,$this-> Comp_stat);
 $updated = PatientQuery::doupdatePatients($conn, $this->patient_id,$this->BP, $this->fname, $this->lname,$this->mname,$this->home_address, $this->users_gender);

        if ($updated) {
            return json_encode(["success" => true]);
        } else {
            return "404";
        }
    }
    private function hasDoSkipped(){
         $conn = $this->connect();
        $data = PatientQuery::SkippedTickets($conn, $this->patient_id,  $this->remarks, $this->status);
        if ($data) {
            return json_encode($data);
        } else {
            return "404";
        }
    }
 
    private function doDisplaypendingTickets() {
        $conn = $this->connect();
        $data = PatientQuery::getPendingPatients($conn, $this->Pend_stat, $this->Serv_stat);
        if ($data) {
            return json_encode($data);
        } else {
            return "404";
        }
    }

    private function hasUpdated() {
        $conn = $this->connect();

        $updated = PatientQuery::doupdatePatientss($conn, $this->patient_id, $this->Serv_stat);

        if ($updated) {
            return json_encode(["success" => true]);
        } else {
            return "404";
        }
    }

}
?>
