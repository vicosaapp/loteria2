<?php
session_start();

function verificar_login($tipo = null) {
    if (!isset($_SESSION['logado'])) {
        header("Location: ../index.php");
        exit;
    }

    if ($tipo && $_SESSION['tipo'] !== $tipo) {
        header("Location: ../" . $_SESSION['tipo'] . "/index.php");
        exit;
    }
}