<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
?>
<?php include '../includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Customer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 800px;
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
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #5a5af1;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .button {
            padding: 8px 15px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .view-button {
            background-color: #4CAF50;
        }
        .return-button {
            background-color: #ff9800;
        }
        .no-results {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin-top: 20px;
        }
    </style>
    <script>
        function searchCustomer() {
            const searchTerm = document.getElementById('search_term').value;
            const resultsContainer = document.getElementById('results');
            
            if (searchTerm.length < 1) {
                resultsContainer.innerHTML = '';
                return;
            }
            fetch(`search_customer_api.php?search_term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        let tableContent = `
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Guest Name</th>
                                        <th>Room Number</th>
                                        <th>Arrival</th>
                                        <th>Departure</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        data.forEach(row => {
                            tableContent += `
                                <tr>
                                    <td>${row.id}</td>
                                    <td>${row.guest_name}</td>
                                    <td>${row.room_number}</td>
                                    <td>${row.arrival_datetime}</td>
                                    <td>${row.departure_datetime}</td>
                                    <td class="action-buttons">
                                        <a href="view_customer_details.php?id=${row.id}" class="button view-button">View</a>
                                        <a href="returning_customer.php?returning_customer=${row.id}" class="button return-button">Register</a>
                                    </td>
                                </tr>
                            `;
                        });
                        tableContent += `</tbody></table>`;
                        resultsContainer.innerHTML = tableContent;
                    } else {
                        resultsContainer.innerHTML = `<p class="no-results">No customers found.</p>`;
                    }
                })
                .catch(err => console.error('Error fetching search results:', err));
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Search Customer</h2>
        <form onsubmit="return false;">
            <input type="text" id="search_term" placeholder="Search by name, room number, or ID" onkeyup="searchCustomer()">
        </form>
        <div id="results"></div>
    </div>
</body>
</html>
