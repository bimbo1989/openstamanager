<?php

use Modules\Contratti\Contratto;

if (isset($id_record)) {
    $contratto = Contratto::find($id_record);

    $record = $dbo->fetchOne('SELECT *,
       (SELECT tipo FROM an_anagrafiche WHERE idanagrafica = co_contratti.idanagrafica) AS tipo_anagrafica,
       (SELECT is_fatturabile FROM co_staticontratti WHERE id=id_stato) AS fatturabile,
       (SELECT is_pianificabile FROM co_staticontratti WHERE id=id_stato) AS pianificabile,
       (SELECT descrizione FROM co_staticontratti WHERE id=id_stato) AS stato,
       (SELECT GROUP_CONCAT(my_impianti_contratti.idimpianto) FROM my_impianti_contratti WHERE idcontratto = co_contratti.id) AS idimpianti
   FROM co_contratti WHERE id='.prepare($id_record));
}
