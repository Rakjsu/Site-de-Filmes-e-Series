Cypress.Commands.add('loginAsAdmin', () => {
  cy.visit('http://localhost/login.php');
  cy.get('input[name="username"]').type('admin');
  cy.get('input[name="password"]').type('123456');
  cy.get('button[type="submit"]').click();
  cy.url().should('include', '/admin/index.php');
});

Cypress.Commands.add('cadastrarUsuario', (nome, email, senha) => {
  cy.visit('/register.php');
  cy.get('input[name="username"]').type(nome);
  cy.get('input[name="email"]').type(email);
  cy.get('input[name="password"]').type(senha);
  cy.get('input[name="confirm_password"]').type(senha);
  cy.get('button[type="submit"]').click();
});

Cypress.Commands.add('criarSerie', (titulo, temporadas) => {
  cy.loginAsAdmin();
  cy.visit('/admin/series.php');
  cy.get('button.adicionar-serie').click();
  cy.get('input[name="titulo"]').type(titulo);
  cy.get('input[name="temporadas"]').type(temporadas);
  cy.get('button[type="submit"]').click();
}); 