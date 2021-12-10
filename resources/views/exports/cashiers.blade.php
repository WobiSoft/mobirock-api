<table>
    <thead>
        <tr>
            <th style="width: 80px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                ID
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; ">
                Primer Nombre
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; ">
                Segundo Nombre
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; ">
                Primer Apellido
            </th>
            <th style="width: 150px; font-weight: bold; background-color: #ff0961; color: #ffffff; ">
                Segundo Apellido
            </th>
            <th style="width: 120px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                No. de Usuario
            </th>
            <th style="width: 200px; font-weight: bold; background-color: #ff0961; color: #ffffff; ">
                Correo Electrónico
            </th>
            <th style="width: 120px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Teléfono Móvil
            </th>
            <th style="width: 120px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Teléfono Fijo
            </th>
            <th style="width: 140px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Creado el
            </th>
            <th style="width: 110px; font-weight: bold; background-color: #ff0961; color: #ffffff; text-align: center;">
                Estatus
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($cashiers as $cashier)
        <tr>
            <td style="text-align: center;">
                {{ $cashier->id }}
            </td>
            <td style="">
                {{ $cashier->first_name }}
            </td>
            <td style="">
                {{ $cashier->second_name }}
            </td>
            <td style="">
                {{ $cashier->first_surname }}
            </td>
            <td style="">
                {{ $cashier->second_surname }}
            </td>
            <td style="text-align: center;">
                {{ $cashier->username }}
            </td>
            <td style="">
                {{ $cashier->email }}
            </td>
            <td style="text-align: center;">
                {{ $cashier->mobile }}
            </td>
            <td style="text-align: center;">
                {{ $cashier->phone }}
            </td>
            <td style="text-align: center;">
                {{ date('Y-m-d', strtotime($cashier->created_at)) }}
            </td>
            <td style="text-align: center;">
                {{ $cashier->config->blocked ? 'Bloqueado' : 'Activo' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
