function getGenre(checkbox) {
    return checkbox.closest('.card-body').querySelector('h6').textContent.trim() === "Garçon" ? 1 : 2;
}

var form = document.getElementById("form");

function regenererCadeaux(){
    let cadeauxToRegenerate = [];
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(function(checkbox) {
        let commandeId = checkbox.value;
        let genre = getGenre(checkbox);
        cadeauxToRegenerate.push({ id_commande: commandeId, genre: genre });
    });
    if (cadeauxToRegenerate.length > 0) {
        let url = "regenerer-cadeaux?";
        cadeauxToRegenerate.forEach(function(cadeau, index) {
            url += `cadeaux[${index}][id_commande]=${cadeau.id_commande}&cadeaux[${index}][genre]=${cadeau.genre}&`;
        });
        url = url.slice(0, -1);
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let retour = JSON.parse(xhr.responseText);
                let div= document.getElementById("cadeaux-list");
                div.innerHTML="";
                div.innerHTML=retour;
                alert("Cadeaux régénérés avec succès !");
            }
        };
        xhr.open("GET", url, true);
        xhr.send();
    } else {
        alert("Veuillez sélectionner des cadeaux à régénérer.");
    }
}
