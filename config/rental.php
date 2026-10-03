<?php

return [

    // O dia da operação precisa ser o de São Paulo; APP_TIMEZONE permanece UTC para não deslocar timestamps de locacoes.
    'timezone' => env('RENTAL_TIMEZONE', 'America/Sao_Paulo'),

];
