<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
?>
<?php
include '../includes/navbar.php';
require_once '../classes/Rooms.php';
require_once '../classes/Customers.php';
// Fetch available rooms
$rooms = new Rooms();
$availableRooms = $rooms->getAvailableRooms();
// Check if it's a returning customer
$customers = new Customers();
$returningCustomerId = $_GET['returning_customer'] ?? null;
$returningCustomer = null;
if ($returningCustomerId) {
    $returningCustomer = $customers->getCustomerById($returningCustomerId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
    <link rel="stylesheet" href="../styles/navbar.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #5a5af1;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
        }
        input, select, button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            background-color: #5a5af1;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #4949c8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?= $returningCustomer ? 'Returning Customer' : 'Add Customer' ?></h2>
        <form action="../controllers/customers_controller.php" method="POST">
            <input type="hidden" name="action" value="add_customer">
            
            <label for="guest_name">Guest Name:</label>
            <input type="text" id="guest_name" name="guest_name" value="<?= htmlspecialchars($returningCustomer['guest_name'] ?? '') ?>" required>
                        
            <label for="nationality">Nationality:</label>
            <select id="nationality" name="nationality" required>
                <option value="<?= $returningCustomer['nationality'] ?>"><?= $returningCustomer['nationality'] ?></option>
                <option value="Afghan">Afghan</option>
                <option value="Albanian">Albanian</option>
                <option value="Algerian">Algerian</option>
                <option value="American">American</option>
                <option value="Andorran">Andorran</option>
                <option value="Angolan">Angolan</option>
                <option value="Antiguans">Antiguans</option>
                <option value="Argentinean">Argentinean</option>
                <option value="Armenian">Armenian</option>
                <option value="Australian">Australian</option>
                <option value="Austrian">Austrian</option>
                <option value="Azerbaijani">Azerbaijani</option>
                <option value="Bahamian">Bahamian</option>
                <option value="Bahraini">Bahraini</option>
                <option value="Bangladeshi">Bangladeshi</option>
                <option value="Barbadian">Barbadian</option>
                <option value="Barbudans">Barbudans</option>
                <option value="Batswana">Batswana</option>
                <option value="Belarusian">Belarusian</option>
                <option value="Belgian">Belgian</option>
                <option value="Belizean">Belizean</option>
                <option value="Beninese">Beninese</option>
                <option value="Bhutanese">Bhutanese</option>
                <option value="Bolivian">Bolivian</option>
                <option value="Bosnian">Bosnian</option>
                <option value="Brazilian">Brazilian</option>
                <option value="British">British</option>
                <option value="Bruneian">Bruneian</option>
                <option value="Bulgarian">Bulgarian</option>
                <option value="Burkinabe">Burkinabe</option>
                <option value="Burmese">Burmese</option>
                <option value="Burundian">Burundian</option>
                <option value="Cambodian">Cambodian</option>
                <option value="Cameroonian">Cameroonian</option>
                <option value="Canadian">Canadian</option>
                <option value="Cape Verdean">Cape Verdean</option>
                <option value="Central African">Central African</option>
                <option value="Chadian">Chadian</option>
                <option value="Chilean">Chilean</option>
                <option value="Chinese">Chinese</option>
                <option value="Colombian">Colombian</option>
                <option value="Comoran">Comoran</option>
                <option value="Congolese">Congolese</option>
                <option value="Costa Rican">Costa Rican</option>
                <option value="Croatian">Croatian</option>
                <option value="Cuban">Cuban</option>
                <option value="Cypriot">Cypriot</option>
                <option value="Czech">Czech</option>
                <option value="Danish">Danish</option>
                <option value="Djibouti">Djibouti</option>
                <option value="Dominican">Dominican</option>
                <option value="Dutch">Dutch</option>
                <option value="East Timorese">East Timorese</option>
                <option value="Ecuadorean">Ecuadorean</option>
                <option value="Egyptian">Egyptian</option>
                <option value="Emirian">Emirian</option>
                <option value="Equatorial Guinean">Equatorial Guinean</option>
                <option value="Eritrean">Eritrean</option>
                <option value="Estonian">Estonian</option>
                <option value="Ethiopian">Ethiopian</option>
                <option value="Fijian">Fijian</option>
                <option value="Filipino">Filipino</option>
                <option value="Finnish">Finnish</option>
                <option value="French">French</option>
                <option value="Gabonese">Gabonese</option>
                <option value="Gambian">Gambian</option>
                <option value="Georgian">Georgian</option>
                <option value="German">German</option>
                <option value="Ghanaian">Ghanaian</option>
                <option value="Greek">Greek</option>
                <option value="Grenadian">Grenadian</option>
                <option value="Guatemalan">Guatemalan</option>
                <option value="Guinea-Bissauan">Guinea-Bissauan</option>
                <option value="Guinean">Guinean</option>
                <option value="Guyanese">Guyanese</option>
                <option value="Haitian">Haitian</option>
                <option value="Herzegovinian">Herzegovinian</option>
                <option value="Honduran">Honduran</option>
                <option value="Hungarian">Hungarian</option>
                <option value="I-Kiribati">I-Kiribati</option>
                <option value="Icelander">Icelander</option>
                <option value="Indian">Indian</option>
                <option value="Indonesian">Indonesian</option>
                <option value="Iranian">Iranian</option>
                <option value="Iraqi">Iraqi</option>
                <option value="Irish">Irish</option>
                <option value="Israeli">Israeli</option>
                <option value="Italian">Italian</option>
                <option value="Ivorian">Ivorian</option>
                <option value="Jamaican">Jamaican</option>
                <option value="Japanese">Japanese</option>
                <option value="Jordanian">Jordanian</option>
                <option value="Kazakhstani">Kazakhstani</option>
                <option value="Kenyan">Kenyan</option>
                <option value="Kittian and Nevisian">Kittian and Nevisian</option>
                <option value="Kuwaiti">Kuwaiti</option>
                <option value="Kyrgyz">Kyrgyz</option>
                <option value="Laotian">Laotian</option>
                <option value="Latvian">Latvian</option>
                <option value="Lebanese">Lebanese</option>
                <option value="Liberian">Liberian</option>
                <option value="Libyan">Libyan</option>
                <option value="Liechtensteiner">Liechtensteiner</option>
                <option value="Lithuanian">Lithuanian</option>
                <option value="Luxembourger">Luxembourger</option>
                <option value="Macedonian">Macedonian</option>
                <option value="Malagasy">Malagasy</option>
                <option value="Malawian">Malawian</option>
                <option value="Malaysian">Malaysian</option>
                <option value="Maldivian">Maldivian</option>
                <option value="Malian">Malian</option>
                <option value="Maltese">Maltese</option>
                <option value="Marshallese">Marshallese</option>
                <option value="Mauritanian">Mauritanian</option>
                <option value="Mauritian">Mauritian</option>
                <option value="Mexican">Mexican</option>
                <option value="Micronesian">Micronesian</option>
                <option value="Moldovan">Moldovan</option>
                <option value="Monacan">Monacan</option>
                <option value="Mongolian">Mongolian</option>
                <option value="Moroccan">Moroccan</option>
                <option value="Mosotho">Mosotho</option>
                <option value="Motswana">Motswana</option>
                <option value="Mozambican">Mozambican</option>
                <option value="Namibian">Namibian</option>
                <option value="Nauruan">Nauruan</option>
                <option value="Nepalese">Nepalese</option>
                <option value="New Zealander">New Zealander</option>
                <option value="Ni-Vanuatu">Ni-Vanuatu</option>
                <option value="Nicaraguan">Nicaraguan</option>
                <option value="Nigerian">Nigerian</option>
                <option value="Nigerien">Nigerien</option>
                <option value="North Korean">North Korean</option>
                <option value="Norwegian">Norwegian</option>
                <option value="Omani">Omani</option>
                <option value="Pakistani">Pakistani</option>
                <option value="Palauan">Palauan</option>
                <option value="Panamanian">Panamanian</option>
                <option value="Papua New Guinean">Papua New Guinean</option>
                <option value="Paraguayan">Paraguayan</option>
                <option value="Peruvian">Peruvian</option>
                <option value="Polish">Polish</option>
                <option value="Portuguese">Portuguese</option>
                <option value="Qatari">Qatari</option>
                <option value="Romanian">Romanian</option>
                <option value="Russian">Russian</option>
                <option value="Rwandan">Rwandan</option>
                <option value="Saint Lucian">Saint Lucian</option>
                <option value="Salvadoran">Salvadoran</option>
                <option value="Samoan">Samoan</option>
                <option value="San Marinese">San Marinese</option>
                <option value="Sao Tomean">Sao Tomean</option>
                <option value="Saudi">Saudi</option>
                <option value="Scottish">Scottish</option>
                <option value="Senegalese">Senegalese</option>
                <option value="Serbian">Serbian</option>
                <option value="Seychellois">Seychellois</option>
                <option value="Sierra Leonean">Sierra Leonean</option>
                <option value="Singaporean">Singaporean</option>
                <option value="Slovakian">Slovakian</option>
                <option value="Slovenian">Slovenian</option>
                <option value="Solomon Islander">Solomon Islander</option>
                <option value="Somali">Somali</option>
                <option value="South African">South African</option>
                <option value="South Korean">South Korean</option>
                <option value="Spanish">Spanish</option>
                <option value="Sri Lankan">Sri Lankan</option>
                <option value="Sudanese">Sudanese</option>
                <option value="Surinamer">Surinamer</option>
                <option value="Swazi">Swazi</option>
                <option value="Swedish">Swedish</option>
                <option value="Swiss">Swiss</option>
                <option value="Syrian">Syrian</option>
                <option value="Taiwanese">Taiwanese</option>
                <option value="Tajik">Tajik</option>
                <option value="Tanzanian">Tanzanian</option>
                <option value="Thai">Thai</option>
                <option value="Togolese">Togolese</option>
                <option value="Tongan">Tongan</option>
                <option value="Trinidadian or Tobagonian">Trinidadian or Tobagonian</option>
                <option value="Tunisian">Tunisian</option>
                <option value="Turkish">Turkish</option>
                <option value="Tuvaluan">Tuvaluan</option>
                <option value="Ugandan">Ugandan</option>
                <option value="Ukrainian">Ukrainian</option>
                <option value="Uruguayan">Uruguayan</option>
                <option value="Uzbekistani">Uzbekistani</option>
                <option value="Venezuelan">Venezuelan</option>
                <option value="Vietnamese">Vietnamese</option>
                <option value="Welsh">Welsh</option>
                <option value="Yemenite">Yemenite</option>
                <option value="Zambian">Zambian</option>
                <option value="Zimbabwean">Zimbabwean</option>
            </select>
            
            <label for="id_passport">ID/Passport:</label>
            <input type="text" id="id_passport" name="id_passport" value="<?= htmlspecialchars($returningCustomer['id_passport'] ?? '') ?>">
            
            <label for="arrival_datetime">Arrival Date and Time:</label>
            <input type="datetime-local" id="arrival_datetime" name="arrival_datetime" required>
            
            <label for="departure_datetime">Departure Date and Time:</label>
            <input type="datetime-local" id="departure_datetime" name="departure_datetime">
            
            <label for="room_number">Room Number:</label>
            <select id="room_number" name="room_number" required>
                <option value="<?= $returningCustomer['room_number'] ?>"><?= $returningCustomer['room_number'] ?></option>
                <?php foreach ($availableRooms as $room): ?>
                    <option value="<?= htmlspecialchars($room['id']) ?>">
                        <?= htmlspecialchars($room['room_number']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="room_rate">Room Rate:</label>
            <input type="number" step="100" id="room_rate" name="room_rate" value="<?= htmlspecialchars($returningCustomer['room_rate'] ?? '') ?>" required>
            
            <label for="num_persons">Number of Persons:</label>
            <input type="number" min="1" id="num_persons" name="num_persons" value="<?= htmlspecialchars($returningCustomer['num_persons'] ?? '1') ?>" required>
            
            <label for="num_children">Number of Children:</label>
            <input type="number" min="0" id="num_children" name="num_children" value="<?= htmlspecialchars($returningCustomer['num_children'] ?? '0') ?>">
            
            <label for="mode_of_payment">Mode of Payment:</label>
            <select id="mode_of_payment" name="mode_of_payment">
                <option value="" disabled selected>Select payment mode</option>
                <option value="Cash" <?= $returningCustomer['mode_of_payment'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                <option value="Momo" <?= $returningCustomer['mode_of_payment'] === 'Momo' ? 'selected' : '' ?>>Momo</option>
                <option value="Credit Card" <?= $returningCustomer['mode_of_payment'] === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                <option value="Bank Transfer" <?= $returningCustomer['mode_of_payment'] === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
            </select>
            
            <label for="company_agency">Company/Travel Agency:</label>
            <input type="text" id="company_agency" name="company_agency" value="<?= htmlspecialchars($returningCustomer['company_agency'] ?? '') ?>">
            
            <label for="email_address">Email Address:</label>
            <input type="email" id="email_address" name="email_address" value="<?= htmlspecialchars($returningCustomer['email_address'] ?? '') ?>">
            
            <label for="mobile_number">Mobile Number:</label>
            <input type="text" id="mobile_number" name="mobile_number" value="<?= htmlspecialchars($returningCustomer['mobile_number'] ?? '') ?>">
            
            <button type="submit"><?= $returningCustomer ? 'Register' : 'Add Customer' ?></button>
        </form>
    </div>
</body>
</html>
