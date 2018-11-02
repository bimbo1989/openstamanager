<?php

include_once __DIR__.'/../../../core.php';

switch ($resource) {
    case 'clienti':
            $query = 'SELECT an_anagrafiche.idanagrafica AS id, CONCAT(ragione_sociale) AS descrizione, id_tipo_intervento_default FROM an_anagrafiche INNER JOIN (an_tipianagrafiche_anagrafiche INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.id_tipo_anagrafica=an_tipianagrafiche.id) ON an_anagrafiche.idanagrafica=an_tipianagrafiche_anagrafiche.idanagrafica |where| ORDER BY ragione_sociale';

            foreach ($elements as $element) {
                $filter[] = 'an_anagrafiche.idanagrafica='.prepare($element);
            }

            $where[] = "descrizione='Cliente'";
            if (empty($filter)) {
                $where[] = 'deleted_at IS NULL';
            }

            if (!empty($search)) {
                $search_fields[] = 'ragione_sociale LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'citta LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'provincia LIKE '.prepare('%'.$search.'%');
            }

            $custom['id_tipo_intervento'] = 'id_tipo_intervento_default';

        break;

    case 'fornitori':
            $query = "SELECT an_anagrafiche.idanagrafica AS id, CONCAT(ragione_sociale, IF(citta IS NULL OR citta = '', '', CONCAT(' (', citta, ')'))) AS descrizione, id_tipo_intervento_default FROM an_anagrafiche
            INNER JOIN `an_sedi` ON `an_sedi`.`id`=`an_anagrafiche`.`id_sede_legale`
            INNER JOIN an_tipianagrafiche_anagrafiche ON an_anagrafiche.idanagrafica=an_tipianagrafiche_anagrafiche.idanagrafica
            INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.id_tipo_anagrafica=an_tipianagrafiche.id |where| ORDER BY ragione_sociale";

            foreach ($elements as $element) {
                $filter[] = 'an_anagrafiche.idanagrafica='.prepare($element);
            }

            $where[] = "descrizione='Fornitore'";
            if (empty($filter)) {
                $where[] = 'deleted_at IS NULL';
            }

            if (!empty($search)) {
                $search_fields[] = 'ragione_sociale LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'citta LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'provincia LIKE '.prepare('%'.$search.'%');
            }

            $custom['id_tipo_intervento'] = 'id_tipo_intervento_default';

        break;

    case 'agenti':
            $query = "SELECT an_anagrafiche.idanagrafica AS id, CONCAT(ragione_sociale, IF(citta IS NULL OR citta = '', '', CONCAT(' (', citta, ')'))) AS descrizione, id_tipo_intervento_default FROM an_anagrafiche
            INNER JOIN `an_sedi` ON `an_sedi`.`id`=`an_anagrafiche`.`id_sede_legale`
            INNER JOIN an_tipianagrafiche_anagrafiche ON an_anagrafiche.idanagrafica=an_tipianagrafiche_anagrafiche.idanagrafica
            INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.id_tipo_anagrafica=an_tipianagrafiche.id |where| ORDER BY ragione_sociale";

            foreach ($elements as $element) {
                $filter[] = 'an_anagrafiche.idanagrafica='.prepare($element);
            }

            $where[] = "descrizione='Agente'";
            if (empty($filter)) {
                $where[] = 'deleted_at IS NULL';
            }

            if (!empty($search)) {
                $search_fields[] = 'ragione_sociale LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'citta LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'provincia LIKE '.prepare('%'.$search.'%');
            }

            $results = AJAX::completeResults($query, $where, $filter, $search, $custom);

            // Evidenzia l'agente di default
            if ($superselect['idanagrafica']) {
                $rsa = $dbo->fetchArray('SELECT idagente FROM an_anagrafiche WHERE idanagrafica='.prepare($superselect['idanagrafica']));
                $idagente_default = $rsa[0]['idagente'];
            } else {
                $idagente_default = 0;
            }

            $ids = array_column($results, 'idanagrafica');
            $pos = array_search($idagente_default, $ids);
            if ($pos !== false) {
                $results[$pos]['_bgcolor_'] = '#ff0';
            }
        break;

    case 'tecnici':
            $query = "SELECT an_anagrafiche.idanagrafica AS id, CONCAT(ragione_sociale, IF(citta IS NULL OR citta = '', '', CONCAT(' (', citta, ')'))) AS descrizione, id_tipo_intervento_default FROM an_anagrafiche
            INNER JOIN `an_sedi` ON `an_sedi`.`id`=`an_anagrafiche`.`id_sede_legale`
            INNER JOIN an_tipianagrafiche_anagrafiche ON an_anagrafiche.idanagrafica=an_tipianagrafiche_anagrafiche.idanagrafica
            INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.id_tipo_anagrafica=an_tipianagrafiche.id |where| ORDER BY ragione_sociale";

            foreach ($elements as $element) {
                $filter[] = 'an_anagrafiche.idanagrafica='.prepare($element);
            }

            $where[] = "descrizione='Tecnico'";
            if (empty($filter)) {
                $where[] = 'deleted_at IS NULL';

                //come tecnico posso aprire attività solo a mio nome
                $user = Auth::user();
                if ($user['gruppo'] == 'Tecnici' && !empty($user['idanagrafica'])) {
                    $where[] = 'an_anagrafiche.idanagrafica='.$user['idanagrafica'];
                }
            }

            if (!empty($search)) {
                $search_fields[] = 'ragione_sociale LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'citta LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'provincia LIKE '.prepare('%'.$search.'%');
            }

            // $custom['id_tipo_intervento'] = 'id_tipo_intervento_default';
        break;

    // Nota Bene: nel campo id viene specificato id_tipo_anagrafica-idanagrafica -> modulo Utenti e permessi, creazione nuovo utente
    case 'anagrafiche':
            $query = "SELECT CONCAT(an_tipianagrafiche.id, '-', an_anagrafiche.idanagrafica) AS id, CONCAT_WS('', ragione_sociale, ' (', citta, ' ', provincia, ')') AS descrizione id_tipo_intervento_default FROM an_anagrafiche
            INNER JOIN `an_sedi` ON `an_sedi`.`id`=`an_anagrafiche`.`id_sede_legale`
            INNER JOIN an_tipianagrafiche_anagrafiche ON an_anagrafiche.idanagrafica=an_tipianagrafiche_anagrafiche.idanagrafica
            INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.id_tipo_anagrafica=an_tipianagrafiche.id |where| ORDER BY ragione_sociale";

            foreach ($elements as $element) {
                $filter[] = 'an_anagrafiche.idanagrafica='.prepare($element);
            }

            if (empty($filter)) {
                $where[] = 'deleted_at IS NULL';
            }

            if (!empty($search)) {
                $search_fields[] = 'ragione_sociale LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'citta LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'provincia LIKE '.prepare('%'.$search.'%');
            }

            // $custom['id_tipo_intervento'] = 'id_tipo_intervento_default';
        break;

    case 'sedi':
        if (isset($superselect['idanagrafica'])) {
            $query = "SELECT * FROM (SELECT 0 AS id, 'Sede legale' AS descrizione UNION SELECT id, CONCAT_WS(' - ', nomesede, citta) FROM an_sedi |where|) AS tab |filter| ORDER BY id";

            foreach ($elements as $element) {
                $filter[] = 'id='.prepare($element);
            }

            $where[] = 'idanagrafica='.prepare($superselect['idanagrafica']);

            if (!empty($search)) {
                $search_fields[] = 'nomesede LIKE '.prepare('%'.$search.'%');
                $search_fields[] = 'citta LIKE '.prepare('%'.$search.'%');
            }
        }
        break;

    case 'referenti':
        if (isset($superselect['idanagrafica'])) {
            $query = 'SELECT id, nome AS descrizione FROM an_referenti |where| ORDER BY id';

            foreach ($elements as $element) {
                $filter[] = 'id='.prepare($element);
            }

            $where[] = 'idanagrafica='.prepare($superselect['idanagrafica']);

            if (!empty($search)) {
                $search_fields[] = 'nome LIKE '.prepare('%'.$search.'%');
            }
        }
        break;
}
