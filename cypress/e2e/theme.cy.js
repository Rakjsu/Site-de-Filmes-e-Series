describe('Admin - Alternância de Tema', () => {
  it('deve alternar entre claro e escuro', () => {
    cy.loginAsAdmin();
    cy.visit('/admin/filmes.php');
    cy.get('#theme-toggle-header').click();
    cy.document().its('documentElement').should('have.attr', 'data-theme', 'dark');
    cy.get('#theme-toggle-header').click();
    cy.document().its('documentElement').should('have.attr', 'data-theme', 'light');
  });
}); 