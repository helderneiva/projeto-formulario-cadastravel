<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Feedback;
use PDO;

final class FeedbackRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Grava o feedback e retorna o ID gerado.
     */
    public function salvar(Feedback $feedback): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO feedbacks (usuario_id, mensagem, nota)
             VALUES (:usuario_id, :mensagem, :nota)'
        );
        $stmt->execute([
            'usuario_id' => $feedback->getUsuarioId(),
            'mensagem'   => $feedback->getMensagem(),
            'nota'       => $feedback->getNota(),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Lista os feedbacks mais recentes de um usuário.
     *
     * @return list<Feedback>
     */
    public function listarPorUsuario(int $usuarioId, int $limite = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, usuario_id, mensagem, nota, criado_em
             FROM feedbacks
             WHERE usuario_id = :usuario_id
             ORDER BY criado_em DESC, id DESC
             LIMIT :limite'
        );
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $feedbacks = [];

        foreach ($stmt->fetchAll() as $linha) {
            $feedbacks[] = Feedback::reconstituir(
                id: (int) $linha['id'],
                usuarioId: (int) $linha['usuario_id'],
                mensagem: (string) $linha['mensagem'],
                nota: (int) $linha['nota'],
                criadoEm: (string) $linha['criado_em']
            );
        }

        return $feedbacks;
    }
}