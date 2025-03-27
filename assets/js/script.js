// Sélection des éléments DOM
const fileInput = document.getElementById("file-input");
const chartTypeSelect = document.getElementById("chart-type");
const xColumnSelect = document.getElementById("x-column");
const yColumnSelect = document.getElementById("y-column");
const chartTitleInput = document.getElementById("chart-title");
const updateTitleButton = document.getElementById("update-title");
const chartColorInput = document.getElementById("chart-color");
const exportPngButton = document.getElementById("export-png");
const exportPdfButton = document.getElementById("export-pdf");
const canvas = document.getElementById("chart");

let chartInstance = null; // Instance du graphique actuel
let parsedData = null; // Données CSV stockées globalement

// Gestion des événements
function addEventListenerIfExists(selector, event, handler) {
    const element = document.getElementById(selector);
    if (element) {
        element.addEventListener(event, handler);
    } else {
        console.warn(`Élément avec l'ID "${selector}" introuvable.`);
    }
}

// Vérifiez si vous êtes sur la bonne page
if (window.location.pathname === "/application") {
    // Ajout des gestionnaires d'événements
    addEventListenerIfExists("file-input", "change", handleFile);
    addEventListenerIfExists("chart-type", "change", updateChart);
    addEventListenerIfExists("x-column", "change", updateChart);
    addEventListenerIfExists("y-column", "change", updateChart);
    addEventListenerIfExists("chart-title", "input", updateChart);
    addEventListenerIfExists("update-title", "click", updateChart);
    addEventListenerIfExists("chart-color", "input", updateChart);
    addEventListenerIfExists("export-png", "click", exportToPNG);
    addEventListenerIfExists("export-pdf", "click", exportToPDF);
}

// Fonction pour gérer l'importation du fichier CSV
function handleFile(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const csvData = XLSX.utils.sheet_to_csv(worksheet);
            try {
                parsedData = parseCSV(csvData); // Stocke les données dans parsedData
                if (parsedData.headers.length > 0) {
                    populateColumnSelectors(parsedData.headers);
                    createChart(parsedData);
                } else {
                    alert("Le fichier CSV est vide ou invalide.");
                }
            } catch (error) {
                alert("Erreur lors du traitement du fichier CSV : " + error.message);
            }
        };
        reader.readAsArrayBuffer(file);
    }
}

// Fonction pour parser le CSV
function parseCSV(data) {
    // Détection du séparateur (virgule ou point-virgule)
    const separator = data.includes(";") ? ";" : ",";

    // Découpage des lignes et colonnes en fonction du séparateur détecté
    const rows = data.trim().split("\n").map(row => row.split(separator));
    const headers = rows[0].map(header => header.trim());
    const dataRows = rows.slice(1).map(row =>
        row.map((cell, index) => (index > 0 && !isNaN(cell) ? Number(cell) : cell.trim()))
    );
    const myData = { headers, dataRows }
    console.log({ myData });
    return { headers, dataRows };
}

// Fonction pour nettoyer les données CSV
function cleanCSVData(dataRows) {
    return dataRows.map(row => row.map(value => value.toString().replace(/[^0-9a-zA-Z .-]/g, "").trim()));
}

// Fonction pour remplir les sélecteurs de colonnes
function populateColumnSelectors(headers) {
    xColumnSelect.innerHTML = headers.map(header => `<option value="${header}">${header}</option>`).join("");
    yColumnSelect.innerHTML = headers.map(header => `<option value="${header}">${header}</option>`).join("");

    xColumnSelect.value = headers[0] || "";
    yColumnSelect.value = headers[1] || "";
}

// Fonction pour créer un graphique
function createChart({ headers, dataRows }) {
    const xColumn = xColumnSelect.value;
    const yColumn = yColumnSelect.value;

    const xIndex = headers.indexOf(xColumn);
    const yIndex = headers.indexOf(yColumn);

    if (xIndex === -1 || yIndex === -1) {
        alert("Veuillez sélectionner des colonnes valides.");
        return;
    }

    const cleanedDataRows = cleanCSVData(dataRows);
    const labels = cleanedDataRows.map(row => row[xIndex]);
    const values = cleanedDataRows.map(row => parseFloat(row[yIndex]));

    if (values.some(value => isNaN(value))) {
        alert("La colonne Y doit contenir uniquement des nombres.");
        return;
    }

    const ctx = canvas.getContext("2d");
    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(ctx, {
        type: chartTypeSelect.value,
        data: {
            labels: labels,
            datasets: [
                {
                    label: chartTitleInput.value || "Graphique",
                    data: values,
                    backgroundColor:
                        chartTypeSelect.value === "pie" ? generateRandomColors(values.length) : chartColorInput.value,
                    borderColor: chartColorInput.value,
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: chartTypeSelect.value !== "bar",
                },
                title: {
                    display: !!chartTitleInput.value,
                    text: chartTitleInput.value,
                },
            },
            scales:
                chartTypeSelect.value !== "pie"
                    ? {
                        y: {
                            beginAtZero: true,
                        },
                    }
                    : {},
        },
    });
}

// Fonction pour générer des couleurs aléatoires
function generateRandomColors(length) {
    return Array.from({ length }, () => {
        const hue = Math.floor(Math.random() * 360);
        return `hsl(${hue}, 70%, 50%)`;
    });
}

// Fonction pour mettre à jour le graphique
function updateChart() {
    if (!parsedData) {
        alert("Veuillez d'abord importer un fichier CSV.");
        return;
    }

    createChart(parsedData); // Recrée le graphique avec les nouvelles options
}

// Exporter le graphique en PNG
function exportToPNG() {
    const link = document.createElement("a");
    link.download = "graphique.png";
    link.href = canvas.toDataURL("image/png");
    link.click();
}

// Exporter le graphique en PDF
function exportToPDF() {
    const { jsPDF } = window.jspdf; // Import depuis le contexte global
    const chartImage = canvas.toDataURL("image/png");

    const pdf = new jsPDF();
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();

    const imgWidth = pageWidth - 20;
    const imgHeight = (canvas.height / canvas.width) * imgWidth;

    pdf.addImage(chartImage, "PNG", 10, 10, imgWidth, imgHeight);
    pdf.save("graphique.pdf");
}

if (window.location.pathname === "/application") {
    document.getElementById('generateAIReport').addEventListener('click', async () => {
        const objective = document.getElementById('analysisObjective').value;

        // Vérifie si les données sont valides
        if (!parsedData || !parsedData.headers || !parsedData.dataRows) {
            alert("Veuillez importer un fichier CSV ou XLSX valide.");
            return;
        }

        // Vérifie si l'objectif est défini
        if (!objective) {
            alert("Veuillez entrer un objectif d'analyse.");
            return;
        }

        // Prépare les données à envoyer à l'API
        const dataSummary = {
            headers: parsedData.headers,
            dataRows: parsedData.dataRows,
        };

        // Prépare la requête à envoyer à l'API
        const requestPayload = {
            dataSummary,
            objective,
        };

        try {
            if (userIsConnected === false) {
                // Si l'utilisateur n'est pas connecté
                mockResponse = {
                    status: 403,
                    json: async () => ({
                        error: 'Vous devez être connecté.',
                        redirectUrl: '/connexion',
                    }),
                };
            } else if (reportAttempts < 1) {
                // Si l'utilisateur est connecté mais n'a plus d'essais
                mockResponse = {
                    status: 403,
                    json: async () => ({
                        error: 'Vous avez utilisé tous vos essais. Veuillez souscrire à un plan.',
                        redirectUrl: '/tarifs',
                    }),
                };
            } else {
                // Si l'utilisateur est connecté et a encore des essais, faire la requête réelle
                mockResponse = await fetch("/api/generate-report", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(requestPayload),
                });
            }

            const response = mockResponse;

            if (!response.ok) {
                // Si la réponse de l'API n'est pas ok (code HTTP 4xx ou 5xx)
                const result = await response.json();
                if (response.status === 403) {
                    // Gestion des erreurs spécifiques, comme utilisateur non connecté ou essais épuisés
                    flasher.warning(result.error || "Une erreur est survenue.", { timeout: 5000 });
                    // Optionnel : rediriger l'utilisateur après un délai
                    if (result.redirectUrl) {
                        setTimeout(() => {
                            window.location.href = result.redirectUrl;
                        }, 5000);
                    }
                } else {
                    throw new Error(`Erreur HTTP : ${response.status}`);
                }
                return;
            }

            // Lire la réponse JSON de l'API
            const result = await response.json();
            console.log(result); // Vérifie la structure de la réponse de l'API

            // Si la génération du rapport a réussi
            if (result && result.message) {
                flasher.success(result.message, { timeout: 5000 });
                // Décrémenter le nombre d'essais restants
                reportAttempts -= 1;
                document.querySelector('p').innerText = `Nombre d'essais restants : ${reportAttempts}`;
            } else {
                flasher.success("Rapport généré avec succès.", { timeout: 5000 });
            }

            // Formater le rapport reçu
            let report = result.report;
            const title = "Rapport d'Analyse";
            if (report.startsWith(title)) {
                report = report.replace(title, `<h1 class="report-title">${title}</h1>`);
            }

            // Ajouter des balises pour les sous-titres et autres modifications de format
            const formattedReport = report
                .replace(/\n/g, '<br>') // Remplacer les sauts de ligne par <br>
                .replace(/\*\*(.+?)\*\*/g, '<h2>$1</h2>') // Remplacer **texte** par <h2>texte</h2>
                .replace(/(ventes les plus élevées|profit le plus élevé|les ventes les plus faibles|tendances générales|recommandations)/gi, '<strong>$1</strong>') // Mettre en gras certains mots
                .replace(/Résumé:/g, '<h3>Résumé</h3>') // Remplacer "Résumé:" par <h3>Résumé</h3>
                .replace(/Tendances générales:/g, '<h3>Tendances Générales</h3>') // Remplacer "Tendances générales:" par <h3>Tendances Générales</h3>
                .replace(/Recommandations:/g, '<h3>Recommandations</h3>') // Remplacer "Recommandations:" par <h3>Recommandations</h3>
                .replace(/- (.+?)(?=\n|$)/g, '<li>$1</li>') // Convertir chaque "- texte" en <li>texte</li>
                .replace(/(<li>.*<\/li>)/g, '<ul>$&</ul>'); // Envelopper la liste dans <ul> uniquement s'il y a des <li>

            // Insérer le rapport formaté dans l'élément HTML
            document.getElementById('reportOutput').innerHTML = formattedReport;
        } catch (error) {
            // Gestion des erreurs de réseau ou autres erreurs d'exécution
            console.error("Erreur lors de l'envoi de la requête à l'API :", error);
            flasher.warning("Une erreur est survenue lors de la génération du rapport.", { timeout: 5000 });
        }
    });
} else {
    console.info('Information : Le script de génération de rapport ne s’exécute pas car vous n’êtes pas sur la page de gestion de l\'application.');
}