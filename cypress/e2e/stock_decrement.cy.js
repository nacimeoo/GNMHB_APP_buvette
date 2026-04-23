describe('Flux de Stock : Décrémentation après paiement', () => {
  const produitNom = 'Bière Pression 25cl'; 

  beforeEach(() => {
    cy.visit('http://localhost/GNMHB/login.html');
    cy.get('input#email').type('nacime@gmail.com');
    cy.get('input#password').type('nacime');
    cy.get('button[type="submit"]').click();
    
    // S'assurer qu'un événement est démarré, sinon les produits ne s'affichent pas
    cy.get('body').then(($body) => {
      if ($body.find('#form-start-event').is(':visible')) {
        cy.get('#event-nom').type('Test Stock');
        cy.get('button').contains('Démarrer').click();
      }
    });
  });

  it('devrait diminuer le stock de 1 après un achat réussi', () => {
    let stockInitial;
    
    // --- ÉTAPE 1 : LIRE LE STOCK ---
    cy.intercept('GET', '**/api/getProduit.php').as('getProduits');
    cy.visit('http://localhost/GNMHB/stock.php');
    cy.wait('@getProduits');
    
    cy.contains('td.fw-semibold', produitNom)
      .parent('tr') 
      .find('td')   
      .eq(4)        
      .invoke('text')
      .then((text) => {
        stockInitial = parseInt(text.trim());
        cy.log(`Stock initial : ${stockInitial}`);
      });
    
    // --- ÉTAPE 2 : FAIRE L'ACHAT ---
    cy.visit('http://localhost/GNMHB/index.php'); 
    
    cy.contains('.product-item__name', produitNom)
      .parents('.product-item')
      .find('.btn-add-cart')
      .click();
    
    // Vérifiez simplement que le produit est dans le panier déjà visible
    cy.get('.cart-item-list').should('contain', produitNom);
    
    // Cliquer sur le bouton "Payer Carte"
    cy.contains('button', 'Payer Carte').click();
    
    // --- ÉTAPE 3 : VÉRIFICATION ---
    cy.visit('http://localhost/GNMHB/stock.php');
    
    cy.contains('td.fw-semibold', produitNom)
      .parent('tr')
      .find('td')
      .eq(4) 
      .invoke('text')
      .then((text) => {
        const stockFinal = parseInt(text.trim());
        expect(stockFinal).to.eq(stockInitial - 1);
      });
  });
});