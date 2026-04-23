describe('Flux Complet : Client', () => {
  const produitNom = 'Bière Pression 25cl'; 

  beforeEach(() => {
    // 1. CONNEXION ADMIN
    cy.visit('http://localhost/GNMHB/login.html');
    cy.get('input#email').type('nacime@gmail.com');
    cy.get('input#password').type('nacime');
    cy.get('button[type="submit"]').click();
    
    // 2. LANCEMENT DE L'ÉVÉNEMENT
    cy.get('body').then(($body) => {
      if ($body.find('#form-start-event').is(':visible')) {
        // ON DÉCLARE L'INTERCEPT AVANT LE CLIC
        cy.intercept('POST', '**/api/startEvent.php').as('eventStarted');
        
        cy.get('#event-nom').type('Event Test Client');
        cy.get('button').contains('Démarrer').click();
        
        // MAINTENANT ON PEUT ATTENDRE
        cy.wait('@eventStarted');
      }
    });
  });

  it('devrait permettre au client de commander une fois l événement lancé et apparaitre dans les transactions', () => {
    // 3. NAVIGATION PAGE CLIENT
    cy.visit('http://localhost/GNMHB/pageClient.php');

    cy.intercept('GET', '**/api/getAllproduit.php*').as('getProdClient');
    cy.wait('@getProdClient');

    // 4. AJOUT AU PANIER
    cy.contains('.pc-produit', produitNom)
      .find('.btn-add-cart')
      .click();

    // 5. VALIDATION ET PAIEMENT
    cy.intercept('POST', '**/api/payerSimule.php*').as('saveCommande'); 
    
    cy.get('button[onclick="ouvrirPanier()"]').click({force: true});
    
    cy.get('#clientNom').type('TestNom');
    cy.get('#clientPrenom').type('TestPrenom');
    cy.get('#clientEmail').type('test@email.com');
    
    cy.contains('button', 'Payer par carte').click();

    // 6. VÉRIFICATION DU SUCCÈS ET CAPTURE DU TICKET EXACT
    cy.wait('@saveCommande').then((interception) => {
      // On vérifie que la requête a réussi
      expect(interception.response.statusCode).to.equal(200);
      
      // On extrait le numéro de ticket de la réponse JSON du serveur
      const numTicketGenere = interception.response.body.numTicket;
      
      // On sauvegarde ce numéro de ticket dans un "alias" Cypress pour s'en resservir
      cy.wrap(numTicketGenere).as('monTicket');
    });

    // 7. VÉRIFICATION CÔTÉ ADMIN (Tableau des transactions)
    cy.visit('http://localhost/GNMHB/vente.php');

    // On rappelle l'alias que l'on a sauvegardé à l'étape 6
    cy.get('@monTicket').then((ticketAcheteur) => {
      
      // On cible le tableau, puis on cherche spécifiquement la LIGNE ('tr') qui contient ce ticket
      cy.get('#transactionsTableBody')
        .contains('tr', ticketAcheteur) 
        .should('be.visible')
        .and('contain', 'TestNom')
        .and('contain', 'CB'); // On vérifie que sur cette même ligne, on a bien le nom et le moyen de paiement
    });
  });

});
