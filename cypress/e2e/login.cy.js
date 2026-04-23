describe('Test de la page de connexion', () => {
  
  // Avant chaque test, Cypress visite la page de login
  beforeEach(() => {
    // Remplacez l'URL par celle de votre serveur local (ex: http://localhost/GNMHB/login.html)
    // Si vous utilisez le serveur web intégré de Cypress, vous pouvez juste mettre un chemin relatif
    cy.visit('http://localhost/GNMHB/login.html') 
  })

  it('Vérifie que la page s\'affiche correctement', () => {
    // Vérifie le titre
    cy.get('h1').should('contain', 'Se connecter')
    
    // Vérifie la présence des champs de saisie
    cy.get('input#email').should('be.visible')
    cy.get('input#password').should('be.visible')
    
    // Vérifie le bouton de soumission
    cy.get('button[type="submit"]').should('contain', 'Se connecter')
  })

  it('Affiche les erreurs de validation quand on soumet un formulaire vide', () => {
    // Clique sur le bouton sans rien remplir
    cy.get('button[type="submit"]').click()

    // Vérifie que les messages d'erreur Bootstrap s'affichent bien
    // (Ceci teste l'intégration de votre fichier login.js)
    cy.get('input#email').siblings('.invalid-feedback')
      .should('be.visible')
      .and('contain', 'Email invalide')

    cy.get('input#password').siblings('.invalid-feedback')
      .should('be.visible')
      .and('contain', 'Mot de passe requis')
  })

  it('Soumet le formulaire avec des données valides', () => {
    // Saisit les informations
    cy.get('input#email').type('nacime@gmail.com')
    cy.get('input#password').type('nacime')

    // Comme le formulaire redirige vers api/log.php, on peut intercepter la requête
    // pour vérifier qu'elle part bien, sans quitter Cypress
    cy.intercept('POST', '**/api/log.php').as('loginRequest')

    // Soumet le formulaire
    cy.get('button[type="submit"]').click()

    // Vérifie que la requête POST a bien été envoyée avec le bon email
    cy.wait('@loginRequest').its('request.body').should('include', 'email=nacime%40gmail.com')  

    cy.url().should('include', '/index.php')
  })
})