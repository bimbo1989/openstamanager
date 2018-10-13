<?php

class AnagraficheCest
{
    /**
     * Crea una nuova anagrafica.
     *
     * @param AcceptanceTester $t
     */
    protected function addAnag(AcceptanceTester $t, $name = 'ANAGRAFICA DI PROVA', $tipo = 1, $partita_iva = '')
    {
        // Effettua l'accesso con le credenziali fornite
        $t->login('admin', 'admin');

        // Seleziona il modulo da aprire
        $t->navigateTo('Anagrafiche');

        // Apre la schermata di nuovo elemento
        $t->clickAndWaitModal('.btn-primary', '#tabs');

        // Completa i campi per il nuovo elemento
        $t->fillField('Ragione sociale', $name);
        $t->select2('#idtipoanagrafica', $tipo);
        $t->click('.btn-box-tool');
        $t->waitForElementVisible('#piva', 3);
        $t->fillField('Partita IVA', $partita_iva);

        // Effettua il submit
        $t->clickAndWait('Aggiungi', '#add-form');

        // Controlla il salvataggio finale
        $t->seeInField('Ragione sociale', $name);
    }

    /**
     * Crea una nuova anagrafica di tipo cliente e la elimina.
     *
     * @param AcceptanceTester $t
     */
    protected function addAndDeleteAnag(AcceptanceTester $t, $name = 'ANAGRAFICA DI PROVA', $tipo = 1, $partita_iva = '')
    {
        $this->addAnag($t, $name, $tipo, $partita_iva);

        // Seleziona l'azione di eliminazione
        $t->clickAndWaitSwal('Elimina', '#tab_0');

        // Conferma l'eliminazione
        $t->clickSwalButton('Elimina');

        // Controlla eliminazione
        $t->see('Anagrafica eliminata!', '.alert-success');
    }

    /**
    * Crea una nuova anagrafica di tipo cliente.
    *
    * @param AcceptanceTester $t
    */
    public function testAnagraficaCliente(AcceptanceTester $t)
    {
        $this->addAnag($t, 'Cliente', 1, '05024030289');
    }

    /**
    * Crea una nuova anagrafica di tipo cliente.
    *
    * @param AcceptanceTester $t
    */
    public function testAnagraficaTecnico(AcceptanceTester $t)
    {
        $this->addAnag($t, 'Tecnico', 2, '05024030289');
    }

    /**
    * Crea una nuova anagrafica di tipo cliente.
    *
    * @param AcceptanceTester $t
    */
    public function testAnagraficaFornitore(AcceptanceTester $t)
    {
        $this->addAnag($t, 'Fornitore', 3, '05024030289');
    }
}
