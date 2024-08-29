<?php

$driver = "pgsql";
$host = "localhost";
$dbName = "give-me-cat";
$user = "sail";
$password = "password";

// Connect to the database

try {
    $pdo = new PDO(
        "$driver:host=$host;dbname=$dbName",
        $user,
        $password,
        [
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch (PDOException $exception) {
    echo $exception->getMessage();
    exit;
}

$res = $pdo->exec("CREATE TABLE IF NOT EXISTS users
    (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        birth_year INT NOT NULL
    )
");

$pdo->exec("DELETE FROM users");

// Preparing a statement with bound data
$name = "Ash";
$year = 1999;
$statement = $pdo->prepare("INSERT INTO users (name, birth_year) VALUES (:name, :birth_year)");
$statement->bindParam(":name", $name);
$statement->bindParam(":birth_year", $year);
$statement->execute();

// Populate table with dummy data

$data = [];

for($i = 0; $i <= 20; $i++) {
    $data[] = [
        bin2hex(random_bytes(15)),
        random_int(1996, 1999)
    ];

}

$statement = $pdo->prepare("INSERT INTO users (name, birth_year) VALUES (?, ?)");
foreach ($data as $row) {
    $statement->execute($row);
}

// Number of affected rows
$query = $pdo->query("SELECT * FROM users");

echo "Got {$query->rowCount()} results:\n\n";

foreach ($query as $result) {
    echo "{$result['name']},\n";
}
echo "\n\n";

// Grouping, pagination
$statement = $pdo->prepare("SELECT birth_year, COUNT(*) FROM users GROUP BY birth_year");
$statement->execute();
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    echo "{$result['birth_year']} - {$result['count']},\n";
}
echo "\n\n";

$statement = $pdo->prepare("SELECT * FROM users ORDER BY birth_year DESC OFFSET 0 LIMIT 5");
$statement->execute();
$results = $statement->fetchAll();
foreach ($results as $result) {
    echo "{$result['id']} - {$result['birth_year']} - {$result['name']},\n";
}
echo "\n\n";

$statement = $pdo->prepare("SELECT * FROM users ORDER BY birth_year DESC OFFSET 5 LIMIT 5");
$statement->execute();
$results = $statement->fetchAll();
foreach ($results as $result) {
    echo "{$result['id']} - {$result['birth_year']} - {$result['name']},\n";
}
echo "\n\n";


// Transactions
$pdo->beginTransaction();
$pdo->exec("UPDATE users set name = 'test_test'");
$pdo->rollBack();

// Iteration over fetch()
$query = $pdo->prepare("SELECT * FROM users");
$query->execute();
while($next = $query->fetch()) {
    echo "{$next['name']},\n";
}