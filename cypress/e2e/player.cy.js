describe('Player - Marcar Cena', () => {
  it('deve abrir um vídeo e exibir o player', () => {
    cy.visit('/filmes.php?id=1'); // Ajuste para a rota real do player se necessário
    cy.get('.player, #player, .video-player').should('be.visible');
  });
}); 