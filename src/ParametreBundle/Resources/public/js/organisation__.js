$(function() {
    var orgSource = [];
    var orgChart = new getOrgChart(document.getElementById("organisation"), {
        scale: 0.6,
        insertNodeEvent: insertNodeEvent,
        createNodeEvent: createNodeEvent,
        updateNodeEvent: updateNodeEvent,
        primaryFields: ["Nom", "Titre"],
        photoFields: ["Image"],
        idField: "OrgId",
        parentIdField: "ParentOrgId"
    });

    var url = Routing.generate('parametre_organisation_liste');

    setTimeout(function() {
        fetch(url, {
            method: 'GET',
            credentials: 'include'
        }).then(function(data) {
            return data.json();
        }).then(function(response) {
            orgChart.loadFromJSON(response);
        }).catch(function(error) {
            console.log(error);
        });
    }, 50);

    function insertNodeEvent(sender, args) {
        var node = args.node;
        console.log(sender);
        if (sender.nodes.hasOwnProperty(args.node.pid)) {
            var parent = sender.nodes[args.node.pid];
            console.log(parent);
            var url = Routing.generate('parametre_organisation_create', {parent: parent.id});
            fetch(url, {
                method: 'POST',
                credentials: 'include'
            }).then(function (data) {
                return data.json();
            }).then(function (response) {
                // orgSource = response;
                // orgChart.loadFromJSON(orgSource, false);
            }).catch(function (error) {
                console.log(error);
                orgChart.removeNode(node.id);
                show_info("Erreur", "Une erreur est survenue", "error");
                return false;
            });
        }
    }

    function createNodeEvent(sender, args) {
        // console.log("create node");
    }

    function updateNodeEvent(sender, args) {
        // console.log("update node event");
        // args.node.data["Nom"] = "Nom modifié";
        //return false;
        //return fasle if you want to cancel the event
    }
});