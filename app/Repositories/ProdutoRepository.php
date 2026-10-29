<?php

namespace App\Repositories;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Collection;

class ProdutoRepository
{
    public function search(array $filtros = []): Collection
    {
        $query = Produto::query();

        // Filtro base: nome (busca parcial)
        if (!empty($filtros['nome'])) {
            $query->where('nome', 'like', '%' . $filtros['nome'] . '%');
        }

        // NOVO filtro 1: preço mínimo
        if (isset($filtros['preco_min'])) {
            $query->where('preco', '>=', $filtros['preco_min']);
        }

        // NOVO filtro 2: somente produtos com estoque
        if (!empty($filtros['em_estoque'])) {
            $query->where('estoque', '>', 0);
        }

        return $query->get();
    }

    public function update(Produto $produto, array $dados): Produto
    {
        $produto->update($dados);

        return $produto->fresh();
    }

    public function delete(Produto $produto): bool
    {
        return (bool) $produto->delete();
    }
}