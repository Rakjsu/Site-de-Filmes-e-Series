describe('Admin - Logout', () => {
  it('deve sair do painel admin', () => {
    cy.loginAsAdmin();
    cy.visit('/admin/filmes.php');
    cy.get('.logout-btn').click();
    cy.url().should('include', '/login.php');
  });
}); 