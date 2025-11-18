<?php
// ----------------------------------------------------
// Database Configuration
// ----------------------------------------------------
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web_experiment_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// ----------------------------------------------------
// Search Logic and Query Construction
// ----------------------------------------------------
$search_term = '';
$where_clause = '';
$search_input_value = ''; // To preserve the input field value

// Check if a search query was submitted via GET
if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    // 1. Sanitize the user input
    $user_query = trim($_GET['query']);
    $search_input_value = htmlspecialchars($user_query); // For displaying back in the form

    // 2. Prepare the search term for LIKE: prepend and append wildcards (%)
    // This is the value we will bind in the prepared statement
    $search_term = '%' . $user_query . '%';

    // 3. Define the WHERE clause: search in title OR content OR author name
    $where_clause = " WHERE p.title LIKE ? OR p.content LIKE ? OR u.name LIKE ?";
}

// 4. Construct the full SQL query
$sql = "SELECT 
            p.title, 
            p.content, 
            p.created_at, 
            u.name AS author_name, 
            u.email AS author_email
        FROM 
            posts p
        JOIN 
            users u ON p.author_id = u.id" 
        . $where_clause . // Append the WHERE clause if searching
        " ORDER BY 
            p.created_at DESC";

// ----------------------------------------------------
// Execute Query using Prepared Statements (if searching)
// ----------------------------------------------------
if ($where_clause) {
    // Use prepared statement for security
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("❌ Error preparing statement: " . $conn->error);
    }

    // Bind parameters: three 's' (string) for title LIKE, content LIKE, and name LIKE
    $stmt->bind_param("sss", $search_term, $search_term, $search_term); 
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Execute standard query if no search term provided
    $result = $conn->query($sql);
}

// Close the connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Experiment 7: Dynamic Content & Search</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; color: #343a40; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #17a2b8; text-align: center; margin-bottom: 30px; }
        .search-form { display: flex; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px; overflow: hidden; }
        .search-form input[type="text"] { flex-grow: 1; padding: 12px 15px; border: none; font-size: 16px; outline: none; }
        .search-form button { background-color: #17a2b8; color: white; border: none; padding: 12px 20px; cursor: pointer; transition: background-color 0.3s; }
        .search-form button:hover { background-color: #138496; }
        .post { background-color: #fff; border: 1px solid #dee2e6; border-left: 5px solid #17a2b8; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        .post h2 { color: #343a40; margin-top: 0; border-bottom: 1px dotted #e9ecef; padding-bottom: 8px; }
        .post-meta { font-size: 0.9em; color: #6c757d; margin-bottom: 15px; }
        .post-meta strong { color: #17a2b8; }
        .post-content { line-height: 1.6; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="container">
        <h1>🔍 Blog Content Search</h1>
        
        <!-- Search Form -->
        <form action="blog.php" method="GET" class="search-form">
            <input type="text" name="query" placeholder="Search titles, content, or authors..." value="<?php echo $search_input_value; ?>">
            <button type="submit">Search</button>
        </form>

        <?php
        if ($search_input_value) {
            echo "<p>Displaying results for: <strong>\"" . $search_input_value . "\"</strong></p>";
        }

        if ($result->num_rows > 0) {
            // Loop through all fetched rows
            while($row = $result->fetch_assoc()) {
                ?>
                <div class="post">
                    <h2><?php echo htmlspecialchars($row["title"]); ?></h2>
                    
                    <div class="post-meta">
                        Posted by 
                        <strong><?php echo htmlspecialchars($row["author_name"]); ?></strong> 
                        on 
                        <span><?php echo date("F j, Y, g:i a", strtotime($row["created_at"])); ?></span>
                    </div>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($row["content"])); ?>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p style='text-align: center;'>No posts found matching your criteria.</p>";
        }
        ?>

    </div>

</body>
</html>