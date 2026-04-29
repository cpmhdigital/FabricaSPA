<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriaPrimaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pedido_id' => 'required|exists:pedidos,id',
            'pedido_item_unidade_id' => 'required|exists:pedido_item_unidade,id',
            'etapa_id' => 'required|exists:etapa,id',
            'usuario_id' => 'required|exists:users,id',

            'mps' => 'required|array|min:1',
            'mps.*.mp_id' => 'required|exists:itens,id',
            'mps.*.valor' => 'required|numeric|min:0.01',
            'mps.*.unidade' => 'required|string|max:10',
            'mps.*.lote' => 'required|string|max:50',
        ];
    }
}
