<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Services\ProdutoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProdutoServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProdutoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProdutoService();
    }

    private function criarProduto(): Produto
    {
        return Produto::create([
            'nome'        => 'Caneta',
            'descricao'   => 'Caneta azul',
            'preco_custo' => 100,
            'preco'       => 150,
            'estoque'     => 10,
        ]);
    }

    public function test_nao_cria_produto_com_preco_abaixo_da_margem_minima(): void
    {
        try {
            $this->service->create([
                'nome'        => 'Caneta',
                'descricao'   => 'Caneta azul',
                'preco_custo' => 100,
                'preco'       => 109.99,
                'estoque'     => 10,
            ]);

            $this->fail('Deveria ter lançado ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('preco', $e->errors());
        }

        $this->assertDatabaseCount('produtos', 0);
    }

    public function test_atualiza_produto_com_preco_valido(): void
    {
        $produto = $this->criarProduto();

        $atualizado = $this->service->update($produto, [
            'nome'  => 'Caneta Premium',
            'preco' => 110, // exatamente o mínimo (100 + 10%)
        ]);

        $this->assertEquals('Caneta Premium', $atualizado->nome);
        $this->assertEquals(110, $atualizado->preco);
        $this->assertDatabaseHas('produtos', [
            'id'   => $produto->id,
            'nome' => 'Caneta Premium',
        ]);
    }

   
    public function test_nao_atualiza_produto_com_preco_abaixo_da_margem_minima(): void
    {
        $produto = $this->criarProduto();

        try {
            $this->service->update($produto, ['preco' => 105]);

            $this->fail('Deveria ter lançado ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('preco', $e->errors());
        }

        $this->assertEquals(150, $produto->fresh()->preco);
    }
}