<?php
session_start();
require '../backend/backend.php';

if (isset($_POST['choice'])) {
    $choice = $_POST['choice'];

    switch ($choice) {
        case 'get_pendingTickets':
            // Assuming there's a class named "Login" in backend.php
        //     if (isset($_POST['username'], $_POST['pwd'])){
        //         $username = $_POST['username'];
        //         $password = $_POST['pwd'];
                $pending = new login();
              echo $pending->getpendingTickets();
                
        //   }
    
            break;
            case 'do_displayTv': 
                if(isset($_POST['patient_id'])){
                    $id = $_POST['patient_id'];
                $pending = new login();
                 echo $pending->doupdateStatusTickets($id);
                }
                break;
           case 'completed_tickets': 
    // Cleaned up duplicates, removed the space in 'Weight', and added 'gender'
    if(isset(
        $_POST['patient_id'], $_POST['fname'], $_POST['lname'], $_POST['mname'], 
        $_POST['BOD'], $_POST['age'], $_POST['civil_Status'], $_POST['contact_no'], 
        $_POST['home_address'], $_POST['BP'], $_POST['gender'], 
        // $_POST['sym_fever'], $_POST['has_cough'], $_POST['has_sorethroat'], 
        // $_POST['has_shortnessBreath'], $_POST['has_influenza_Symptoms'], 
        // $_POST['has_history_Covid'], $_POST['have_localTransimission'], 
        // $_POST['have_contact_recentTravel'], $_POST['has_inluenza_illness'], 
        // $_POST['has_contactConfirm'], $_POST['medicine'], 
        // $_POST['existingConditions'], $_POST['admissionDate'], 
        // $_POST['admitted_conditions'], $_POST['historyICU'], 
        // $_POST['Weight'],$_POST['Pressure']
    )){
        $id = $_POST['patient_id'];
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $mname = $_POST['mname'];
        $BOD = $_POST['BOD'];
        $age = $_POST['age'];
        $civil_Status = $_POST['civil_Status'];
        $contact_no = $_POST['contact_no'];
        $home_address = $_POST['home_address'];
        $BP = $_POST['BP'];
        $pressure = $_POST['Pressure'];
        $Weight = $_POST['Weight']; // Fixed key name (removed space)
        $sym_fever = $_POST['sym_fever'];
        $has_cough = $_POST['has_cough'];
        $has_sorethroat = $_POST['has_sorethroat'];
        $has_shortnessBreath = $_POST['has_shortnessBreath'];
        $has_influenza_Symptoms = $_POST['has_influenza_Symptoms'];

        $has_history_Covid = $_POST['has_history_Covid'];
        $have_localTransimission = $_POST['have_localTransimission'];
        $have_contact_recentTravel = $_POST['have_contact_recentTravel'];
        $has_inluenza_illness = $_POST['has_inluenza_illness'];
        $has_contactConfirm = $_POST['has_contactConfirm'];

        $user_gender = $_POST['gender'];
        $medicine = $_POST['medicine'];
        $existingConditions = $_POST['existingConditions'];
        $admissionDate = $_POST['admissionDate'];
        $admitted_conditions = $_POST['admitted_conditions'];
        $historyICU = $_POST['historyICU'];
        // $took_antipyretics = $_POST['took_antipyretics'];

        // Uncomment your logic here
        // $pending = new login();
        // echo $pending->completedTickets($id);
        $pending = new login();
        echo $pending->completedTickets(
            $id, 
            // $BOD, 
            // $age, 
            // $civil_Status, 
            // $contact_no, 
           
            $BP, 
             $fname, 
            $lname, 
            $mname, 
           $user_gender,
            $home_address, 
             $age, 
             $BOD,
             $pressure,
              $Weight,
            $civil_Status, 
            $contact_no, 

             $sym_fever,
             $has_cough,
             $has_sorethroat,
             $has_shortnessBreath,
             $has_influenza_Symptoms,

             $has_history_Covid,
              $have_localTransimission,
               $have_contact_recentTravel,
               $has_inluenza_illness,
                $has_contactConfirm,

                 $medicine,
                 $existingConditions,
                 $admissionDate,
                 $admitted_conditions,
                 $historyICU



        );
       
    } else {
        // Debugging tip: Uncomment the line below to see what data PHP is actually receiving
        // var_dump($_POST); 
        echo "not connected";
    }
    break;
            case 'skip_ticket':
                 if(isset($_POST['patient_id'],$_POST['remarks'],$_POST['status'])){
                   $id = $_POST['patient_id'];
                   $remarks = $_POST['remarks'];
                   $stat = $_POST['status'];
                   $pending = new login();
                   echo $pending->SkippedTickets($id,$remarks,$stat);
                }else{
                    echo "not connected";
                }
                break;
     
        

        default:
            echo "Invalid choice";
            break;
    }
} else {
    echo "Choice not set";
}
