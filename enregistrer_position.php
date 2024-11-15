<?php
// Activer l'affichage des erreurs pour le débogage (à désactiver en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ajouter les en-têtes CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Réponse en JSON

// Fonction pour valider les entrées
function validate_input($data, $type = 'string') {
    switch ($type) {
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT) !== false;
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT) !== false;
        default:
            return !empty(trim($data));
    }
}

// Vérifier si les données nécessaires sont envoyées
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    if (!validate_input($user_id, 'int') || !validate_input($latitude, 'float') || !validate_input($longitude, 'float')) {
        echo json_encode(["status" => "error", "message" => "Données invalides"]);
        exit;
    }

    // Connexion à la base de données
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "geolocation_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérification de la connexion
    if ($conn->connect_error) {
        echo json_encode(["status" => "error", "message" => "Erreur de connexion à la base de données"]);
        exit;
    }

    // Requête préparée pour insérer les données
    $stmt = $conn->prepare("INSERT INTO positions (user_id, latitude, longitude, timestamp) VALUES (?, ?, ?, ?)");
    $timestamp = date("Y-m-d H:i:s");
    $stmt->bind_param("ssss", $user_id, $latitude, $longitude, $timestamp);

    // Exécuter la requête et vérifier si elle a réussi
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Position enregistrée avec succès"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'enregistrement"]);
    }

    // Fermer la connexion
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
}
