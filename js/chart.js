const moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

function formatEur(val) {
    return val.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
}

// ── Stat cards ────────────────────────────────────────────────────────────────
fetch('api/getStatsMois.php')
    .then(r => r.json())
    .then(data => {
        document.getElementById('stat-encaisse').textContent   = formatEur(parseFloat(data.totalEncaisse));
        document.getElementById('stat-cb').textContent         = formatEur(parseFloat(data.totalCB));
        document.getElementById('stat-espece').textContent     = formatEur(parseFloat(data.totalEspece));
        document.getElementById('stat-impayes').textContent    = formatEur(parseFloat(data.totalImpayes));
        document.getElementById('stat-commandes').textContent  = parseInt(data.nbCommandes);
    })
    .catch(() => {
        ['stat-encaisse','stat-cb','stat-espece','stat-impayes','stat-commandes']
            .forEach(id => { document.getElementById(id).textContent = '—'; });
    });

// ── Rapport Financier Annuel ──────────────────────────────────────────────────
fetch('api/getFinanceAnnuel.php')
    .then(r => r.json())
    .then(data => {
        const options = {
            series: [
                { name: 'Encaissé',  data: data.encaisse },
                { name: 'Impayés',   data: data.impayes  },
                { name: 'Total',     data: data.total    }
            ],
            chart: {
                height: 400,
                type: 'line',
                toolbar: { show: true, tools: { download: true, selection: true } },
                zoom: { enabled: false }
            },
            colors: ['#7EC8E3', '#D4AF37', '#1A2D4F'],
            stroke: { curve: 'smooth', width: [3, 3, 4] },
            xaxis: { categories: moisLabels },
            yaxis: {
                labels: { formatter: val => val.toLocaleString('fr-FR') + ' €' }
            },
            tooltip: {
                y: { formatter: val => formatEur(val) }
            },
            legend: { position: 'top', horizontalAlign: 'center' }
        };
        new ApexCharts(document.querySelector('#financeChart'), options).render();
    });

// ── Top 3 Meilleurs Produits ──────────────────────────────────────────────────
fetch('api/getTopProduits.php')
    .then(r => r.json())
    .then(data => {
        const pieOptions = {
            series: data.quantites,
            chart: { width: 380, type: 'donut' },
            labels: data.noms,
            colors: ['#7EC8E3', '#1A2D4F', '#D4AF37'],
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: val => val + ' ventes' } },
            legend: { position: 'bottom' },
            responsive: [{
                breakpoint: 480,
                options: { chart: { width: 300 }, legend: { position: 'bottom' } }
            }],
            noData: { text: 'Aucune vente enregistrée' }
        };
        new ApexCharts(document.querySelector('#topProductsChart'), pieOptions).render();
    });

// ── Stock Actuel par Produit ──────────────────────────────────────────────────
fetch('api/getStockActuel.php')
    .then(r => r.json())
    .then(data => {
        const stockOptions = {
            series: [{ name: 'Quantité en stock', data: data.quantites }],
            chart: { type: 'bar', height: 400, toolbar: { show: true } },
            plotOptions: {
                bar: { borderRadius: 4, columnWidth: '45%', distributed: true }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: data.noms,
                labels: { style: { fontSize: '12px', fontWeight: 600 } }
            },
            yaxis: { title: { text: 'Unités en stock' } },
            colors: ['#7EC8E3', '#4A90C4', '#1A2D4F', '#9FC4DC', '#D4AF37'],
            legend: { show: false },
            tooltip: { y: { formatter: val => val + ' unités' } },
            noData: { text: 'Aucun stock disponible' }
        };
        new ApexCharts(document.querySelector('#stockEvolutionChart'), stockOptions).render();
    });
