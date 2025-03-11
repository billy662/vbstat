<?php
    // Include database connection
    include 'conn.php';

    // Initialize response array
    $response = array(
        'status' => 'success',
        'data' => array(),
        'message' => ''
    );

    try {
        // Prepare and execute query
        $sql = "SELECT rid, aid FROM role_action";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        // Fetch data
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response['data'][] = array(
                    "rid" => $row["rid"], 
                    "aid" => $row["aid"]
                );
            }
            $response['message'] = 'Data retrieved successfully';
        } else {
            $response['message'] = 'No records found';
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    } finally {
        // Close connection if it exists and is open
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
    }

    // Set content type header and output JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
?>