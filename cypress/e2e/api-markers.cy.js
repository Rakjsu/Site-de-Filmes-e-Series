describe('API Markers', () => {
  it('deve retornar markers de exemplo para um filme', () => {
    cy.request('GET', 'http://localhost/api/get-markers.php?contentId=movie1&contentType=movie')
      .its('status').should('eq', 200);
    cy.request('GET', 'http://localhost/api/get-markers.php?contentId=movie1&contentType=movie')
      .its('body').should('include', 'markers');
  });
}); 