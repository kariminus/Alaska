<?php
// Return all articles
function getArticles() {
    $bdd = new PDO('mysql:host=localhost;dbname=alaska;charset=utf8', 'alaska_user', 'secret');
    $articles = $bdd->query('select * from t_article order by art_id desc');
    return $articles;
}