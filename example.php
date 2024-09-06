<?php

require 'vendor/autoload.php';

use QueryBuilder\QueryBuilder;
use QueryBuilder\Connections\PDOConnection;

 

function insertUser(QueryBuilder $queryBuilder, PDOConnection $connection, array $userData): int
{
    $queryBuilder
        ->insertInto('users')
        ->values($userData)
        ->execute();

    return $connection->lastInsertId();
}

function selectUsers(QueryBuilder $queryBuilder, int $minAge, int $limit): array
{
    $result = $queryBuilder
        ->select(['id', 'name', 'email'])
        ->from('users')
        ->where('age', '>', $minAge)
        ->orderBy('name', 'ASC')
        ->limit($limit)
        ->execute();

    return $result->fetchAll();
}

function updateUserStatus(QueryBuilder $queryBuilder, int $userId, string $newStatus): int
{
    $result = $queryBuilder
        ->update('users')
        ->set(['status' => $newStatus])
        ->where('id', '=', $userId)
        ->execute();

    return $result->rowCount();
}

function deleteYoungUsers(QueryBuilder $queryBuilder, int $maxAge): int
{
    $result = $queryBuilder
        ->deleteFrom('users')
        ->where('age', '<', $maxAge)
        ->execute();

    return $result->rowCount();
}

function main()
{
    try {
        $connection = createConnection();
        $queryBuilder = new QueryBuilder($connection);

        // Insert a new user
        $newUserId = insertUser($queryBuilder, $connection, [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
            'status' => 'not active'
        ]);
        echo "INSERT Sonucu: Eklenen kullanıcı ID: $newUserId\n";

        $youngUserId = insertUser($queryBuilder, $connection, [
            'name' => 'Young User',
            'email' => 'young@example.com',
            'age' => 17,
            'status' => 'active'
        ]);

        // Select users
        $users = selectUsers($queryBuilder, 18, 10);
          
        echo "SELECT Sonuçları:\n";
        foreach ($users as $user) {
            echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}\n";
        }

        // Update user status
        $updatedRows = updateUserStatus($queryBuilder, $newUserId, 'active');
        echo "UPDATE Sonucu: Güncellenen satır sayısı: $updatedRows\n";

        // Delete young users
        $deletedRows = deleteYoungUsers($queryBuilder, 30);
       
        echo "DELETE Sonucu: Silinen satır sayısı: $deletedRows\n";

    } catch (Exception $e) {
        echo "Bir hata oluştu: " . $e->getMessage() . "\n";
    }
}

main();