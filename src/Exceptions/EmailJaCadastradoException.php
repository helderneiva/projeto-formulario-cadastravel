<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

/**
 * Lançada quando se tenta cadastrar um e-mail que já existe.
 */
final class EmailJaCadastradoException extends DomainException
{
    public function __construct()
    {
        parent::__construct('E-mail já cadastrado!');
    }
}