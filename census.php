<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connect to database
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->beginTransaction();

        // 🧾 Save household head info
        $stmt = $conn->prepare("
            INSERT INTO census_submissions (
                first_name, last_name, age, gender, contact_no,
                birth_day, birth_month, birth_year,
                province, city, barangay, building, house_lot, street,
                female_death, female_death_age, female_death_cause,
                child_death, child_death_age, child_death_sex, child_death_cause,
                disease_1, disease_2, disease_3,
                need_1, need_2, need_3,
                water_supply, toilet_facility, toilet_other,
                garbage_disposal, lighting_fuel, lighting_other,
                cooking_fuel, cooking_other,
                submitted_at
            ) VALUES (
                :first_name, :last_name, :age, :gender, :contact_no,
                :birth_day, :birth_month, :birth_year,
                :province, :city, :barangay, :building, :house_lot, :street,
                :female_death, :female_death_age, :female_death_cause,
                :child_death, :child_death_age, :child_death_sex, :child_death_cause,
                :disease_1, :disease_2, :disease_3,
                :need_1, :need_2, :need_3,
                :water_supply, :toilet_facility, :toilet_other,
                :garbage_disposal, :lighting_fuel, :lighting_other,
                :cooking_fuel, :cooking_other,
                NOW()
            )
        ");

        $stmt->execute([
            ':first_name' => $_POST['first_name'] ?? '',
            ':last_name' => $_POST['last_name'] ?? '',
            ':age' => $_POST['age'] ?? '',
            ':gender' => $_POST['gender'] ?? '',
            ':contact_no' => $_POST['contact_no'] ?? '',
            ':birth_day' => $_POST['birth_day'] ?? '',
            ':birth_month' => $_POST['birth_month'] ?? '',
            ':birth_year' => $_POST['birth_year'] ?? '',
            ':province' => $_POST['province'] ?? '',
            ':city' => $_POST['city'] ?? '',
            ':barangay' => $_POST['barangay'] ?? '',
            ':building' => $_POST['building'] ?? '',
            ':house_lot' => $_POST['house_lot'] ?? '',
            ':street' => $_POST['street'] ?? '',
            ':female_death' => $_POST['female_death'] ?? '',
            ':female_death_age' => $_POST['female_death_age'] ?? null,
            ':female_death_cause' => $_POST['female_death_cause'] ?? null,
            ':child_death' => $_POST['child_death'] ?? '',
            ':child_death_age' => $_POST['child_death_age'] ?? null,
            ':child_death_sex' => $_POST['child_death_sex'] ?? null,
            ':child_death_cause' => $_POST['child_death_cause'] ?? null,
            ':disease_1' => $_POST['disease_1'] ?? '',
            ':disease_2' => $_POST['disease_2'] ?? '',
            ':disease_3' => $_POST['disease_3'] ?? '',
            ':need_1' => $_POST['need_1'] ?? '',
            ':need_2' => $_POST['need_2'] ?? '',
            ':need_3' => $_POST['need_3'] ?? '',
            ':water_supply' => $_POST['water_supply'] ?? '',
            ':toilet_facility' => $_POST['toilet_facility'] ?? '',
            ':toilet_other' => $_POST['toilet_other'] ?? '',
            ':garbage_disposal' => $_POST['garbage_disposal'] ?? '',
            ':lighting_fuel' => $_POST['lighting_fuel'] ?? '',
            ':lighting_other' => $_POST['lighting_other'] ?? '',
            ':cooking_fuel' => $_POST['cooking_fuel'] ?? '',
            ':cooking_other' => $_POST['cooking_other'] ?? ''
        ]);

        $household_id = $conn->lastInsertId();

        // 👨‍👩‍👧‍👦 Save household members
        $member_stmt = $conn->prepare("
            INSERT INTO household_members (
                household_id, member_name, age, birth_month, birth_year, sex,
                philhealth_have, philhealth_id, pwd_have, pwd_id, relationship,
                civil_status, religion, citizenship, education_level, currently_enrolled,
                school_level, school_place, employment_status, work_details
            ) VALUES (
                :household_id, :member_name, :age, :birth_month, :birth_year, :sex,
                :philhealth_have, :philhealth_id, :pwd_have, :pwd_id, :relationship,
                :civil_status, :religion, :citizenship, :education_level, :currently_enrolled,
                :school_level, :school_place, :employment_status, :work_details
            )
        ");

        for ($i = 1; $i <= 10; $i++) {
            if (!empty($_POST["member_name_$i"])) {
                $member_stmt->execute([
                    ':household_id' => $household_id,
                    ':member_name' => $_POST["member_name_$i"] ?? '',
                    ':age' => $_POST["age_$i"] ?? '',
                    ':birth_month' => $_POST["birth_month_$i"] ?? '',
                    ':birth_year' => $_POST["birth_year_$i"] ?? '',
                    ':sex' => $_POST["sex_$i"] ?? '',
                    ':philhealth_have' => $_POST["philhealth_have_$i"] ?? '',
                    ':philhealth_id' => $_POST["philhealth_id_$i"] ?? null,
                    ':pwd_have' => $_POST["pwd_have_$i"] ?? '',
                    ':pwd_id' => $_POST["pwd_id_$i"] ?? null,
                    ':relationship' => $_POST["relationship_$i"] ?? '',
                    ':civil_status' => $_POST["civil_status_$i"] ?? '',
                    ':religion' => $_POST["religion_$i"] ?? '',
                    ':citizenship' => $_POST["citizenship_$i"] ?? '',
                    ':education_level' => $_POST["education_level_$i"] ?? '',
                    ':currently_enrolled' => $_POST["currently_enrolled_$i"] ?? '',
                    ':school_level' => $_POST["school_level_$i"] ?? '',
                    ':school_place' => $_POST["school_place_$i"] ?? '',
                    ':employment_status' => $_POST["employment_$i"] ?? '',
                    ':work_details' => $_POST["work_details_$i"] ?? ''
                ]);
            }
        }

        $conn->commit();
        echo "<script>alert('✅ Census form submitted successfully!'); window.location.href='census.php';</script>";

    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo "❌ Database error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Census Form - Barangay System</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    body {
        font-family: "Poppins", sans-serif;
        background: #f4f6fa;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 750px;
        background: #fff;
        margin: 40px auto;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    h2, h1 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 25px;
    }
    .form-group {
        margin-bottom: 18px;
    }
    label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
    }
    input[type="text"],
    input[type="number"],
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
    }
    input[readonly] {
        background-color: #f1f1f1;
        color: #555;
    }
    .birthdate-group {
        display: flex;
        gap: 10px;
    }
    .birthdate-group select {
        flex: 1;
    }
    .form-row {
        display: flex;
        gap: 15px;
    }
    .form-row .form-group {
        flex: 1;
    }
    button {
        display: block;
        width: 100%;
        background: #007bff;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }
    button:hover {
        background: #0056b3;
    }
</style>
</head>
<body>

<div class="container">
    <form class="census-form" method="POST" action="">
        <div class="census-container">
            <div class="census-header">
                <h1>Barangay Census Form</h1>
                <p>Please fill out the form below completely</p>
            </div>

            <!-- Basic Info -->
            <div class="form-row">
                <label for="household_head">Household Head Name</label>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" pattern="[A-Za-z ]+" title="Letters only" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" pattern="[A-Za-z ]+" title="Letters only" required>
                </div>
            </div>

            <!-- Birthdate -->
            <div class="form-group">
                <label>Birthdate</label>
                <div class="birthdate-group">
                    <select id="birth_day" name="birth_day" required>
                        <option value="">Day</option>
                        <?php for ($d = 1; $d <= 31; $d++): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                        <?php endfor; ?>
                    </select>

                    <select id="birth_month" name="birth_month" required>
                        <option value="">Month</option>
                        <?php 
                            $months = [
                                1 => 'January', 
                                2 => 'February', 
                                3 => 'March', 
                                4 => 'April', 
                                5 => 'May', 
                                6 => 'June', 
                                7 => 'July', 
                                8 => 'August', 
                                9 => 'September', 
                                10 => 'October', 
                                11 => 'November', 
                                12 => 'December'
                            ];
                            foreach ($months as $num => $name): 
                        ?>
                            <option value="<?= $name ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="birth_year" name="birth_year" required>
                        <option value="">Year</option>
                        <?php 
                            $currentYear = date('Y');
                            for ($y = $currentYear; $y >= 1900; $y--): 
                        ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" min="1" max="120" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="contact_no">Contact No.:</label>
                <input type="text" id="contact_no" name="contact_no" maxlength="11" 
                       pattern="\d{11}" placeholder="Enter 11-digit Contact No." 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
            </div>


            <!-- Location Info -->
            <div class="form-row">
                <div class="form-group">
                    <label for="province">Province</label>
                    <input type="text" id="province" name="province" value="Pampanga" readonly>
                </div>
                <div class="form-group">
                    <label for="city">City/Municipality</label>
                    <input type="text" id="city" name="city" value="San Fernando" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="barangay">Barangay</label>
                <select id="barangay" name="barangay" required>
                    <option value="">Select Barangay</option>
                    <option value="Saguin">Brgy. Saguin</option>
                    <option value="Del Rosario">Brgy. Del Rosario</option>
                    <option value="Sindalan">Brgy. Sindalan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="building">Room/Floor/Unit No. & Building Name</label>
                <input type="text" id="building" name="building">
            </div>

            <div class="form-group">
                <label for="house_lot">House/Lot & Block No.</label>
                <input type="text" id="house_lot" name="house_lot" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>

        <div class="form-group">
            <label for="street">Street Name</label>
            <select id="street" name="street" required>
                <option value="">Select Street</option>
                <option value="">purok 1</option>
                <option value="">purok 2</option>
                <option value="">purok 3</option>
                <option value="">purok 4</option>
                <option value="">purok 5</option>
                <option value="">purok 6</option>
                <option value="">purok 7</option>
                <!-- 📝 Add street names dynamically here later -->
            </select>
        </div>



<!-- Household Members Section -->
<h3>Household Members Information</h3>
<button id="openTableBtn" style="margin-bottom:10px;">📋 View Household Table</button>

<!-- Modal -->
<div id="householdModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Household Members Information</h2>

    <div class="table-container">
      <table class="household-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Household Member</th>
            <th>Age</th>
            <th>Date of Birth</th>
            <th>Sex</th>
            <th>PhilHealth ID</th>
            <th>PWD ID</th>
            <th>Relationship</th>
            <th>Civil Status</th>
            <th>Religion</th>
            <th>Citizenship</th>
            <th>Education Level</th>
            <th>Currently Enrolled?</th>
            <th>School Level</th>
            <th>Place of School</th>
            <th>Employment Status</th>
            <th>Work Details</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 1; $i <= 10; $i++): ?>
          <tr>
            <td><?= $i ?></td>
            <td><input type="text" name="member_name_<?= $i ?>" placeholder="Surname, First name, Middle name"></td>
            <td><input type="number" class="age-input" name="age_<?= $i ?>" min="0" max="120" required></td>

            <!-- Date of Birth -->
            <td>
              <select name="birth_month_<?= $i ?>">
                <option value="">Month</option>
                <?php
                $months = [
                  "January","February","March","April","May","June",
                  "July","August","September","October","November","December"
                ];
                foreach ($months as $m) echo "<option value='$m'>$m</option>";
                ?>
              </select>
              <select name="birth_year_<?= $i ?>">
                <option value="">Year</option>
                <?php for ($y = date('Y'); $y >= 1900; $y--) echo "<option value='$y'>$y</option>"; ?>
              </select>
            </td>

            <!-- Sex -->
            <td>
              <select name="sex_<?= $i ?>">
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
              </select>
            </td>

            <!-- PhilHealth -->
            <td>
              <select name="philhealth_have_<?= $i ?>" class="philhealth-select">
                <option value="">Select</option>
                <option value="yes">Have</option>
                <option value="no">None</option>
              </select>
            </td>

            <!-- PWD -->
            <td>
              <select name="pwd_have_<?= $i ?>" class="pwd-select">
                <option value="">Select</option>
                <option value="yes">Have</option>
                <option value="no">None</option>
              </select>
            </td>

            <!-- Relationship -->
            <td>
              <select name="relationship_<?= $i ?>">
                <option value="">Select</option>
                <option>Head</option><option>Spouse</option><option>Son</option><option>Daughter</option>
                <option>Stepson</option><option>Stepdaughter</option><option>Son-in-law</option>
                <option>Daughter-in-law</option><option>Grandson</option><option>Granddaughter</option>
                <option>Father</option><option>Mother</option><option>Brother</option><option>Sister</option>
                <option>Uncle</option><option>Aunt</option><option>Nephew</option><option>Niece</option>
                <option>Other relative</option><option>Non-relative</option><option>Boarder</option>
                <option>Domestic helper</option>
              </select>
            </td>

            <!-- Civil Status -->
            <td>
              <select name="civil_status_<?= $i ?>">
                <option value="">Select</option>
                <option>Single</option><option>Married</option><option>Living-in</option>
                <option>Widowed</option><option>Separated</option><option>Divorced</option><option>Unknown</option>
              </select>
            </td>

            <td><input type="text" name="religion_<?= $i ?>" placeholder="Religion"></td>
            <td><input type="text" name="citizenship_<?= $i ?>" placeholder="Citizenship"></td>

            <!-- Education -->
            <td class="education-cell"></td>

            <!-- Enrollment -->
            <td class="enrolled-cell"></td>
            <td class="school-level-cell"></td>
            <td class="school-place-cell"></td>

            <!-- Employment -->
            <td>
              <select name="employment_<?= $i ?>" class="employment-select">
                <option value="">Select</option>
                <option value="unemployed">Unemployed</option>
                <option value="self-employed">Self-employed</option>
                <option value="employed">Employed</option>
              </select>
            </td>

            <td class="work-details-cell"></td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
.modal {display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.6);}
.modal-content{background:#fff;margin:2% auto;padding:15px;border-radius:10px;width:95%;max-width:1600px;max-height:90vh;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.4);}
.close{color:red;float:right;font-size:28px;font-weight:bold;cursor:pointer;}
.table-container{overflow:auto;max-height:75vh;border:1px solid #ddd;}
.household-table{border-collapse:collapse;width:100%;font-size:13px;min-width:1800px;}
.household-table th,.household-table td{border:1px solid #ddd;padding:5px;text-align:center;}
.household-table th{background-color:#004080;color:white;position:sticky;top:0;}
.household-table input,.household-table select{width:100%;padding:3px;box-sizing:border-box;}
</style>

<script>
// Modal open/close
const modal=document.getElementById("householdModal");
const btn=document.getElementById("openTableBtn");
const span=document.getElementsByClassName("close")[0];
btn.onclick=()=>{modal.style.display="block";document.body.style.overflow="hidden";}
span.onclick=()=>{modal.style.display="none";document.body.style.overflow="auto";}
window.onclick=e=>{if(e.target==modal){modal.style.display="none";document.body.style.overflow="auto";}}

// LocalStorage save/restore
document.querySelectorAll(".household-table input,.household-table select").forEach(el=>{
  const key=el.name;
  if(localStorage.getItem(key)) el.value=localStorage.getItem(key);
  el.addEventListener("input",()=>localStorage.setItem(key,el.value));
});

// PhilHealth logic
document.querySelectorAll(".philhealth-select").forEach(sel=>{
  sel.addEventListener("change",e=>{
    const cell=e.target.parentElement;
    const old=cell.querySelector(".philhealth-id");
    if(old) old.remove();
    if(e.target.value==="yes"){
      const input=document.createElement("input");
      input.type="text";input.className="philhealth-id";
      input.placeholder="12-digit ID";input.pattern="\\d{12}";input.maxLength=12;
      cell.appendChild(input);
    }
  });
});

// PWD logic
document.querySelectorAll(".pwd-select").forEach(sel=>{
  sel.addEventListener("change",e=>{
    const cell=e.target.parentElement;
    const old=cell.querySelector(".pwd-id");
    if(old) old.remove();
    if(e.target.value==="yes"){
      const input=document.createElement("input");
      input.type="text";input.className="pwd-id";
      input.placeholder="PWD ID";input.pattern="\\d*";
      cell.appendChild(input);
    }
  });
});

// Dynamic age logic
document.querySelectorAll(".age-input").forEach(ageField=>{
  ageField.addEventListener("input",()=>{
    const row=ageField.closest("tr");
    const age=parseInt(ageField.value)||0;
    const eduCell=row.querySelector(".education-cell");
    const enrollCell=row.querySelector(".enrolled-cell");
    const levelCell=row.querySelector(".school-level-cell");
    const placeCell=row.querySelector(".school-place-cell");
    eduCell.innerHTML="";enrollCell.innerHTML="";levelCell.innerHTML="";placeCell.innerHTML="";

    // Education (only 5+ years old)
    if(age>=5){
      const edu=document.createElement("select");
      edu.innerHTML=`
        <option value="">Select</option>
        <option>No education</option>
        <option>Pre-school</option>
        <option>Elementary level</option>
        <option>Elementary graduate</option>
        <option>High school level</option>
        <option>High school graduate</option>
        <option>Junior HS</option>
        <option>Junior HS graduate</option>
        <option>Senior HS level</option>
        <option>Senior HS graduate</option>
        <option>Vocational/Tech</option>
        <option>College level</option>
        <option>College graduate</option>
        <option>Post-graduate</option>`;
      eduCell.appendChild(edu);
    }

    // School (only 3–24 years old)
    if(age>2 && age<25){
      const enroll=document.createElement("select");
      enroll.className="enrolled-select";
      enroll.innerHTML=`
        <option value="">Select</option>
        <option value="yes-public">Yes - Public</option>
        <option value="yes-private">Yes - Private</option>
        <option value="no">No</option>`;
      enrollCell.appendChild(enroll);

      enroll.addEventListener("change",e=>{
        levelCell.innerHTML="";placeCell.innerHTML="";
        if(e.target.value.startsWith("yes")){
          const level=document.createElement("select");
          level.innerHTML=`
            <option value="">Select</option>
            <option>Pre-school</option>
            <option>Elementary</option>
            <option>Junior High School</option>
            <option>Senior High School</option>
            <option>Vocational/Technical</option>
            <option>College/University</option>`;
          levelCell.appendChild(level);
          const place=document.createElement("input");
          place.type="text";place.placeholder="Place of School";
          placeCell.appendChild(place);
        }
      });
    }
  });
});

// Employment logic
document.querySelectorAll(".employment-select").forEach(sel=>{
  sel.addEventListener("change",e=>{
    const cell=e.target.closest("tr").querySelector(".work-details-cell");
    cell.innerHTML="";
    if(e.target.value==="employed"||e.target.value==="self-employed"){
      const input=document.createElement("input");
      input.type="text";input.placeholder="Work / Company Details";
      cell.appendChild(input);
    }
  });
});
</script>




<!-- Questions Section -->
<div class="form-section">
    <h2>Questions</h2>

    <!-- Question 1: Female household member who died -->
    <div class="form-group">
        <label>Do you have any female household member who died in the past 12 months?</label>
        <select id="female_death" name="female_death" required>
            <option value="">Select an answer</option>
            <option value="no">No</option>
            <option value="yes">Yes</option>
        </select>
    </div>

    <div id="female_death_details" style="display: none;">
        <div class="form-group">
            <label for="female_death_age">How old is she?</label>
            <input type="number" id="female_death_age" name="female_death_age" min="1" max="120" placeholder="Enter age">
        </div>
        <div class="form-group">
            <label for="female_death_cause">What is the cause of her death?</label>
            <input type="text" id="female_death_cause" name="female_death_cause" placeholder="Enter cause of death">
        </div>
    </div>

    <!-- Question 2: Child household member who died -->
    <div class="form-group">
        <label>Do you have a child household member below 5 years old who died in the past 12 months?</label>
        <select id="child_death" name="child_death" required>
            <option value="">Select an answer</option>
            <option value="no">No</option>
            <option value="yes">Yes</option>
        </select>
    </div>

    <div id="child_death_details" style="display: none;">
        <div class="form-group">
            <label for="child_death_age">How old is she/he?</label>
            <input type="number" id="child_death_age" name="child_death_age" min="0" max="5" placeholder="Enter age">
        </div>
        <div class="form-group">
            <label for="child_death_sex">Sex:</label>
            <select id="child_death_sex" name="child_death_sex">
                <option value="">Select Sex</option>
                <option value="female">Female</option>
                <option value="male">Male</option>
            </select>
        </div>
        <div class="form-group">
            <label for="child_death_cause">What is the cause of death?</label>
            <input type="text" id="child_death_cause" name="child_death_cause" placeholder="Enter cause of death">
        </div>
    </div>

    <!-- Question 3: Common diseases -->
    <div class="form-group">
        <label>What are the common diseases that cause death in this barangay? (Provide at least 3)</label>
        <table class="numbered-table">
            <tr>
                <td>1.</td>
                <td><input type="text" name="disease_1" placeholder="Enter disease" required></td>
            </tr>
            <tr>
                <td>2.</td>
                <td><input type="text" name="disease_2" placeholder="Enter disease" required></td>
            </tr>
            <tr>
                <td>3.</td>
                <td><input type="text" name="disease_3" placeholder="Enter disease" required></td>
            </tr>
          
        </table>
    </div>

    <!-- Question 4: Primary needs -->
    <div class="form-group">
        <label>What do you think are the primary needs of this barangay? (Provide at least 3)</label>
        <table class="numbered-table">
            <tr>
                <td>1.</td>
                <td><input type="text" name="need_1" placeholder="Enter primary need" required></td>
            </tr>
            <tr>
                <td>2.</td>
                <td><input type="text" name="need_2" placeholder="Enter primary need" required></td>
            </tr>
            <tr>
                <td>3.</td>
                <td><input type="text" name="need_3" placeholder="Enter primary need" required></td>
            </tr>
            
        </table>
    </div>
</div>

<!-- JavaScript for conditional display -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const femaleSelect = document.getElementById('female_death');
    const femaleDetails = document.getElementById('female_death_details');
    const childSelect = document.getElementById('child_death');
    const childDetails = document.getElementById('child_death_details');

    function toggleDetails(select, details) {
        details.style.display = select.value === 'yes' ? 'block' : 'none';
    }

    femaleSelect.addEventListener('change', () => toggleDetails(femaleSelect, femaleDetails));
    childSelect.addEventListener('change', () => toggleDetails(childSelect, childDetails));
});
</script>

<!-- Add this to your CSS file if not yet present -->
<style>
.numbered-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.numbered-table td:first-child {
    width: 30px;
    text-align: center;
    font-weight: bold;
    color: var(--primary-blue);
}

.numbered-table td:last-child input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>


<!-- Household Utilities Section -->
<div class="form-section">
    <h2>Household Utilities and Facilities</h2>


<!-- Main Source of Water Supply -->
<div class="form-group">
    <label for="water_supply">Main Source of Water Supply:</label>
    <select id="water_supply" name="water_supply" required>
        <option value="">Select source</option>
        <option value="tap_inside_house">Tap inside house</option>
        <option value="public_well">Public well</option>
        <option value="private_deep_well">Private deep well</option>
        <option value="jetmatic">Jetmatic</option>
    </select>
</div>

<!-- Type of Toilet Facilities -->
<div class="form-group">
    <label for="toilet_facility">Type of Toilet Facilities:</label>
    <select id="toilet_facility" name="toilet_facility" required>
        <option value="">Select type</option>
        <option value="none">None</option>
        <option value="open_pit">Open pit</option>
        <option value="closed_pit">Closed pit</option>
        <option value="water_sealed_shared">Water-sealed other depository shared</option>
        <option value="water_sealed_exclusive">Water-sealed other depository exclusive</option>
        <option value="water_sealed_septic_shared">Water-sealed sewer septic tank shared</option>
        <option value="water_sealed_septic_exclusive">Water-sealed sewer septic tank exclusive</option>
        <option value="other">Others (specify)</option>
    </select>
    <input type="text" id="toilet_other" name="toilet_other" placeholder="Please specify" style="display: none;">
</div>

<!-- Type of Garbage Disposal -->
<div class="form-group">
    <label for="garbage_disposal">Type of Garbage Disposal:</label>
    <select id="garbage_disposal" name="garbage_disposal" required>
        <option value="">Select type</option>
        <option value="pickup_truck">Pickup by garbage truck</option>
        <option value="burning">Burning</option>
        <option value="composting">Composting</option>
        <option value="burying">Burying</option>
        <option value="segregation">Segregation</option>
    </select>
</div>

<!-- Lighting Fuel -->
<div class="form-group">
    <label for="lighting_fuel">What type of fuel does this household use for lighting?</label>
    <select id="lighting_fuel" name="lighting_fuel" required>
        <option value="">Select type</option>
        <option value="none">None</option>
        <option value="oil">Oil (vegetable, animal, others)</option>
        <option value="lpg">Liquified Petroleum Gas (LPG)</option>
        <option value="kerosene">Kerosene</option>
        <option value="electricity">Electricity</option>
        <option value="other">Others (specify)</option>
    </select>
    <input type="text" id="lighting_other" name="lighting_other" placeholder="Please specify" style="display: none;">
</div>

<!-- Cooking Fuel -->
<div class="form-group">
    <label for="cooking_fuel">What kind of fuel does this household use most of the time for cooking?</label>
    <select id="cooking_fuel" name="cooking_fuel" required>
        <option value="">Select type</option>
        <option value="none">None</option>
        <option value="wood">Wood</option>
        <option value="charcoal">Charcoal</option>
        <option value="lpg">Liquified Petroleum Gas (LPG)</option>
        <option value="kerosene">Kerosene</option>
        <option value="electricity">Electricity</option>
        <option value="other">Others (specify)</option>
    </select>
    <input type="text" id="cooking_other" name="cooking_other" placeholder="Please specify" style="display: none;">
 </div>


   <div class="form-actions">
        <button type="submit">Submit Census Form</button>
    </div>
</form>

<!-- JavaScript for "Others specify" behavior -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selects = [
        { selectId: 'toilet_facility', inputId: 'toilet_other' },
        { selectId: 'lighting_fuel', inputId: 'lighting_other' },
        { selectId: 'cooking_fuel', inputId: 'cooking_other' }
    ];

    selects.forEach(item => {
        const select = document.getElementById(item.selectId);
        const input = document.getElementById(item.inputId);

        select.addEventListener('change', function() {
            if (select.value === 'other') {
                input.style.display = 'block';
            } else {
                input.style.display = 'none';
                input.value = '';
            }
        });
    });
});
</script>

<script>
    // 🧩 Dynamic Street Dropdown based on Barangay
    const barangaySelect = document.getElementById("barangay");
    const streetSelect = document.getElementById("street");

    const streets = {
        "Saguin": ["Purok 1","Purok 2","Purok 3","Purok 4","Purok 5","Purok 6","Purok 7"],
        "Del Rosario": [ "Purok 1","Purok 2","Purok 3","Purok 4","Purok 5","Purok 6","Purok 7"],
        "Sindalan": ["Purok 1","Purok 2","Purok 3","Purok 4","Purok 5","Purok 6","Purok 7"]
    };

    barangaySelect.addEventListener("change", function() {
        const selectedBarangay = this.value;
        streetSelect.innerHTML = '<option value="">Select Street</option>';

        if (streets[selectedBarangay]) {
            streets[selectedBarangay].forEach(street => {
                const option = document.createElement("option");
                option.value = street;
                option.textContent = street;
                streetSelect.appendChild(option);
            });
        }
    });
</script>

</body>
</html>
