<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProdutoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProdutoRepository();
    }

    private function criarProduto(array $dados = []): Produto
    {
        return Produto::create(array_merge([
            'nome'        => 'Caneta',
            'descricao'   => 'Caneta azul',
            'preco_custo' => 100,
            'preco'       => 150,
            'estoque'     => 10,
        ], $dados));
    }

    public function test_search_filtra_por_preco_minimo(): void
    {
        $this->criarProduto(['nome' => 'Barato', 'preco' => 120]);
        $this->criarProduto(['nome' => 'Caro', 'preco' => 300]);

        $resultado = $this->repository->search(['preco_min' => 200]);

        $this->assertCount(1, $resultado);
        $this->assertEquals('Caro', $resultado->first()->nome);
    }

    public function test_search_filtra_apenas_produtos_em_estoque(): void
    {
        $this->criarProduto(['nome' => 'Com estoque', 'estoque' => 5]);
        $this->criarProduto(['nome' => 'Sem estoque', 'estoque' => 0]);

        $resultado = $this->repository->search(['em_estoque' => true]);

        $this->assertCount(1, $resultado);
        $this->assertEquals('Com estoque', $resultado->first()->nome);
    }

    public function test_update_altera_o_produto(): void
    {
        $produto = $this->criarProduto();

        $atualizado = $this->repository->update($produto, ['nome' => 'Caneta Premium']);

        $this->assertEquals('Caneta Premium', $atualizado->nome);
        $this->assertDatabaseHas('produtos', [
            'id'   => $produto->id,
            'nome' => 'Caneta Premium',
        ]);
    }

    public function test_delete_remove_o_produto(): void
    {
        $produto = $this->criarProduto();

        $resultado = $this->repository->delete($produto);

        $this->assertTrue($resultado);
        $this->assertDatabaseMissing('produtos', ['id' => $produto->id]);
    }
}