describe('Admin - CRUD de Filmes', () => {
  it('deve acessar a página de filmes', () => {
    cy.loginAsAdmin();
    cy.visit('/admin/filmes.php');
    cy.contains('Filmes').should('be.visible');
  });
}); 