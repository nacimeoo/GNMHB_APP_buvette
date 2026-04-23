const moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

function formatEur(val) {
    return val.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
}

fetch('api/getStatsMois.php')
    .then(r => r.json())
    .then(data => {
        document.getElementById('stat-vente').textContent    = formatEur(parseFloat(data.totalVente));
        document.getElementById('stat-depense').textContent  = formatEur(parseFloat(data.totalDepense));
        document.getElementById('stat-benef').textContent    = formatEur(parseFloat(data.totalBenef));
        document.getElementById('stat-clients').textContent  = parseInt(data.nbClients);
        document.getElementById('stat-commandes').textContent = parseInt(data.nbCommandes);
    })
    .catch(() => {
        ['stat-vente','stat-depense','stat-benef','stat-clients','stat-commandes']
            .forEach(id => { document.getElementById(id).textContent = '—'; });
    });

fetch('api/getFinanceAnnuel.php')
    .then(r => r.json())
    .then(data => {
        const options = {
            series: [
                { name: 'Vente',   data: data.vente   },
                { name: 'Dépense', data: data.depense },
                { name: 'Bénéf',   data: data.benef   }
            ],
            chart: {
                height: 400,
                type: 'line',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#3b6fd4', '#e53935', '#FFC43F'],
            stroke: { curve: 'smooth', width: [3, 3, 4] },
            xaxis: { categories: moisLabels },
            yaxis: {
                labels: { formatter: val => val.toLocaleString('fr-FR') + ' €' }
            },
            tooltip: {
                y: { formatter: val => formatEur(val) }
            },
            legend: { position: 'top', horizontalAlign: 'center' },
        };
        new ApexCharts(document.querySelector('#financeChart'), options).render();
    });

fetch('api/getTopProduits.php')
    .then(r => r.json())
    .then(data => {
        const pieOptions = {
            series: data.quantites,
            chart: { width: 380, type: 'donut' },
            labels: data.noms,
            colors: ['#3b6fd4', '#1a2235', '#FFC43F'],
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

fetch('api/getStockActuel.php')
    .then(r => r.json())
    .then(data => {
        const stockOptions = {
            series: [{ name: 'Quantité en stock', data: data.quantites }],
            chart: { type: 'bar', height: 400, toolbar: { show: false } },
            plotOptions: {
                bar: { borderRadius: 4, columnWidth: '45%', distributed: true }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: data.noms,
                labels: { style: { fontSize: '12px', fontWeight: 600 } }
            },
            yaxis: { title: { text: 'Unités en stock' } },
            colors: ['#3b6fd4', '#4A90C4', '#1a2235', '#9FC4DC', '#FFC43F'],
            legend: { show: false },
            tooltip: { y: { formatter: val => val + ' unités' } },
            noData: { text: 'Aucun stock disponible' }
        };
        new ApexCharts(document.querySelector('#stockEvolutionChart'), stockOptions).render();
    });

document.getElementById('exportPdfBtn').addEventListener('click', () => {
    window.open('api/exportPdf.php', '_blank');
});

document.getElementById('exportExcelBtn').addEventListener('click', () => {
    window.open('api/exportExcel.php', '_blank');
});

