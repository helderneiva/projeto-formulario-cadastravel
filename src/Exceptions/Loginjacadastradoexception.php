<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

/**
 * Lançada quando se tenta cadastrar um user id que já existe.
 */
final class LoginJaCadastradoException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Este user id já está em uso. Escolha outro.');
    }
}