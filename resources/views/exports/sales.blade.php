<table>
    <thead>
        <tr>
            <th style="width: 120px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: left;">
                Referencia
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; ">
                Compañia
            </th>
            <th style="width: 190px; font-weight: bold; background-color: #ff0961; color: #ffffff; ">
                Tipo
            </th>
            <th style="width: 100px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: right;">
                Saldo inicial
            </th>
            <th style="width: 100px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: right;">
                Monto
            </th>
            <th style="width: 100px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: right;">
                Saldo final
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Folio
            </th>
            <th style="width: 200px; font-weight: bold; background-color: #ff0961; color: #ffffff; ">
                Realizada por
            </th>
            <th style="width: 175px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Fecha y hora
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
        <tr>
            <td style="text-align: left;">
                {{ $sale->reference }}
            </td>
            <td style="">
                {{ $sale->brand->name }}
            </td>
            <td style="">
                {{ $sale->brand->type->name }}
            </td>
            <td style="text-align: right;">
                {{ (!empty($sale->extra['start']) ? $sale->extra['start'] : 0) }}
            </td>
            <td style="text-align: right;">
                {{ $sale->amount }}
            </td>
            <td style="text-align: right;">
                {{ (!empty($sale->extra['end']) ? $sale->extra['end'] : 0) }}
            </td>
            <td style="text-align: center;">
                {{ $sale->authorization_code }}
            </td>
            <td style="">
                {{ $sale->created_by->name }}
            </td>
            <td style="text-align: center;">
                {{ $sale->processed_at }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
