const GOAL_AMOUNT = 400000; // dein Kampagnenziel

fetch('Vertraege_2025.csv')
  .then(response => response.text())
  .then(csv => {
    const rows = csv.trim().split('\n');
    const header = rows[0].split(',');
    const inIndex = header.indexOf('kredite_hinzugekommen');
    const outIndex = header.indexOf('kredite_rausgekommen');
    const dateIndex = header.indexOf('datum');

    const dates = [];
    const netValues = [];
    let totalIn = 0;
    let totalOut = 0;
    let netTotal = 0;

    rows.slice(1).forEach(row => {
      const cols = row.split(',');
      const amountIn = parseFloat(cols[inIndex]) || 0;
      const amountOut = parseFloat(cols[outIndex]) || 0;
      const date = cols[dateIndex] || '';

      totalIn += amountIn;
      totalOut += amountOut;
      netTotal += amountIn - amountOut;

      if (date) {
        dates.push(date);
        netValues.push(netTotal);
      }
    });

    document.getElementById('totalIn').textContent = totalIn.toFixed(2);
    document.getElementById('totalOut').textContent = totalOut.toFixed(2);
    document.getElementById('netTotal').textContent = netTotal.toFixed(2);

    // Entwicklung über Zeit
    new Chart(document.getElementById('lineChart'), {
      type: 'line',
      data: {
        labels: dates,
        datasets: [{
          label: 'Netto Entwicklung 💜',
          data: netValues,
          fill: true,
          backgroundColor: 'rgba(156, 39, 176, 0.2)',
          borderColor: '#9c27b0',
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    // Ziel-Fortschritt
    new Chart(document.getElementById('goalChart'), {
      type: 'doughnut',
      data: {
        labels: ['Erreicht 💜', 'Fehlt'],
        datasets: [{
          data: [netTotal, Math.max(0, GOAL_AMOUNT - netTotal)],
          backgroundColor: ['#4caf50', '#ef9a9a']
        }]
      },
      options: {
        cutout: '70%',
        plugins: {
          tooltip: {
            callbacks: {
              label: (ctx) => `${ctx.label}: ${ctx.parsed.toFixed(2)} €`
            }
          }
        }
      }
    });
  })
  .catch(err => console.error('Fehler beim Laden der CSV:', err));
