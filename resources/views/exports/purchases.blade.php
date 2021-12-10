<table>
    <thead>
        <tr>
            <th style="width: 80px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                ID
            </th>
            <th style="width: 100px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: right;">
                Saldo inicial
            </th>
            <th style="width: 100px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: right;">
                Monto
            </th>
            <th style="width: 100px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: right;">
                Comisión
            </th>
            <th style="width: 100px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: right;">
                Saldo final
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Folio
            </th>
            <th style="width: 125px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Fecha del Pago
            </th>
            <th style="width: 200px; font-weight: bold; background-color: #ff0961; color: #ffffff;">
                Registrada por
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Registrada
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Validada
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Recibida
            </th>
            <th style="width: 175px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Estatus
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchases as $purchase)
        <tr>
            <td style="text-align: center;">
                {{ $purchase->id }}
            </td>
            <td style="text-align: right;">
                {{ (!empty($purchase->transaction->receiver) ? $purchase->transaction->receiver->start : 'N/D') }}
            </td>
            <td style="text-align: right;">
                {{ $purchase->amount }}
            </td>
            <td style="text-align: right;">
                {{ (!empty($purchase->transaction->receiver) ? $purchase->transaction->receiver->fee : 'N/D') }}
            </td>
            <td style="text-align: right;">
                {{ (!empty($purchase->transaction->receiver) ? $purchase->transaction->receiver->final : 'N/D') }}
            </td>
            <td style="text-align: center;">
                {{ $purchase->identifier }}
            </td>
            <td style="text-align: center;">
                {{ date('Y-m-d', strtotime($purchase->date)) }}
            </td>
            <td style="">
                {{ $purchase->registered_by->name }}
            </td>
            <td style="text-align: center;">
                {{ date('Y-m-d H:i:s', strtotime($purchase->registered_at)) }}
            </td>
            <td style="text-align: center;">
                {{ ($purchase->verified_at ? date('Y-m-d H:i:s', strtotime($purchase->verified_at)) : 'N/A') }}
            </td>
            <td style="text-align: center;">
                {{ ($purchase->processed_at ? date('Y-m-d H:i:s', strtotime($purchase->processed_at)) : 'N/A') }}
            </td>
            <td style="text-align: center;">
                {{ $purchase->status->name }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
