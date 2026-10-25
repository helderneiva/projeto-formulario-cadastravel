<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Support\Auth;

// Porta de entrada do sistema: quem está logado vai para o perfil,
// os demais vão para o login.
redirecionar(Auth::usuarioId() !== null ? 'perfil.php' : 'login.php');