# PHP-Search
(Updated) Adds an HTML search form to filter posts. Modifies the SELECT query with a WHERE ... LIKE clause and uses prepared statements to bind the search term (%query%) securely, preventing SQL injection.

### 7. Search Functionality (SECURE READ)

This experiment introduces a search capability, building upon the dynamic content display from Experiment 4.

| File | Description |
| :--- | :--- |
| `blog.php` | The updated script now includes a search input field. It dynamically adjusts the SQL query using a `WHERE ... LIKE` clause. Crucially, it utilizes **prepared statements** to bind the user's search query, ensuring the application is secure against SQL injection attempts, even when using partial matching wildcards. |
