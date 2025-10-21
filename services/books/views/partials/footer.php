  <footer class="main-footer">
    <div class="footer-container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>VirtuaLib</h3>
          <p>Sua biblioteca virtual completa</p>
        </div>
        
        <div class="footer-section">
          <h4>Navegação</h4>
          <ul>
            <li><a href="/books">Início</a></li>
            <li><a href="/books">Livros</a></li>
            <li><a href="/books/search">Buscar</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h4>Contato</h4>
          <p>contato@virtualib.com</p>
          <p>(11) 99999-9999</p>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; 2024 VirtuaLib. Todos os direitos reservados.</p>
      </div>
    </div>
  </footer>

  <style>
    .main-footer {
      background: #2c3e50;
      color: white;
      margin-top: 60px;
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px 20px;
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      margin-bottom: 30px;
    }

    .footer-section h3 {
      margin: 0 0 10px 0;
      font-size: 1.5rem;
      color: #ecf0f1;
    }

    .footer-section h4 {
      margin: 0 0 15px 0;
      font-size: 1.1rem;
      color: #bdc3c7;
    }

    .footer-section p {
      margin: 5px 0;
      color: #95a5a6;
      line-height: 1.5;
    }

    .footer-section ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-section ul li {
      margin: 8px 0;
    }

    .footer-section ul li a {
      color: #95a5a6;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer-section ul li a:hover {
      color: #3498db;
    }

    .footer-bottom {
      border-top: 1px solid #34495e;
      padding-top: 20px;
      text-align: center;
    }

    .footer-bottom p {
      margin: 0;
      color: #7f8c8d;
      font-size: 0.9rem;
    }

    @media (max-width: 768px) {
      .footer-content {
        grid-template-columns: 1fr;
        gap: 20px;
      }
    }
  </style>
</body>
</html>
