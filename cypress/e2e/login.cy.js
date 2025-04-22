describe('Login Admin', () => {
  it('deve fazer login e acessar o painel admin', () => {
    cy.visit('http://localhost/login.php');
    cy.get('input[name="username"]').type('admin');
    cy.get('input[name="password"]').type('123456');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/admin/index.php');
    cy.contains('Bem-vindo ao Painel de Administração').should('be.visible');
  });
}); 