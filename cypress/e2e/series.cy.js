describe('Admin - CRUD de Séries', () => {
  it('deve criar uma nova série', () => {
    const titulo = 'Série Cypress ' + Date.now();
    const temporadas = 3;
    cy.criarSerie(titulo, temporadas);
    cy.contains(titulo).should('be.visible');
  });
}); 