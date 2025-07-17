<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Hierarchy Tree</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        /* Custom styles for the family tree */
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
            overflow: hidden;
            /* Prevent body scroll */
        }

        .chart-container {
            width: 100%;
            height: 90vh;
            /* Adjust height as needed */
            max-width: 1200px;
            /* Max width for larger screens */
            background-color: #ffffff;
            border-radius: 1rem;
            /* Rounded corners */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            /* Hide overflow of SVG */
            position: relative;
        }

        .node circle {
            stroke: #555;
            stroke-width: 1.5px;
            cursor: pointer;
        }

        .node text {
            font-size: 8px;
            /* Smaller font size to 8px */
            fill: #000;
            /* Text color set to black */
            text-anchor: middle;
            pointer-events: none;
            /* Allow clicks to pass through text to circle */
        }

        .link {
            stroke: #999;
            stroke-opacity: 0.6;
            stroke-width: 1.5px;
        }

        .link.spouse-link {
            stroke: #e74c3c;
            /* Red for spouse links */
            stroke-dasharray: 5 5;
            /* Dashed line for spouse */
        }

        .link.parent-child-link {
            stroke: #27ae60;
            /* Green for parent-child links */
        }

        /* Tooltip styles */
        .tooltip {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            /* Tooltip text remains white for contrast on dark background */
            padding: 8px 12px;
            border-radius: 0.5rem;
            font-size: 12px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 10;
        }
    </style>
</head>

<body>
    <div class="chart-container">
        <svg id="family-tree-svg" class="w-full h-full"></svg>
        <div id="tooltip" class="tooltip"></div>
    </div>

    <script>
        // Retrieve the graph data passed from Laravel
        const graphData = @json($graphData);
        const data = JSON.parse(graphData);

        // Set up dimensions for the SVG
        const width = document.querySelector('.chart-container').offsetWidth;
        const height = document.querySelector('.chart-container').offsetHeight;

        // Create SVG element and append it to the container
        const svg = d3.select("#family-tree-svg")
            .attr("viewBox", `0 0 ${width} ${height}`); // Use viewBox for responsiveness

        // Create a group for the graph content that will be zoomed/panned
        const g = svg.append("g");

        // Initialize tooltip
        const tooltip = d3.select("#tooltip");

        // --- Define clipPath for avatars ---
        g.append("defs").selectAll("clipPath")
            .data(data.nodes)
            .enter().append("clipPath")
            .attr("id", d => `clip-${d.id}`)
            .append("circle")
            .attr("r", 25); // Same radius as the node circle

        // --- Calculate depth for each node to enforce vertical hierarchy ---
        const nodeDepths = new Map();
        const queue = [];
        const processedForDepth = new Set(); // To prevent re-processing nodes

        // Find initial root nodes (those without any parent_child link pointing to them in the current data)
        data.nodes.forEach(node => {
            // Check if this node is a target of any parent_child link
            const isChildOfAny = data.links.some(link => link.target.id === node.id && link.type ===
                'parent_child');
            if (!isChildOfAny) {
                nodeDepths.set(node.id, 0); // Assign depth 0 to root nodes
                queue.push(node.id);
                processedForDepth.add(node.id);
            }
        });

        let head = 0;
        while (head < queue.length) {
            const currentId = queue[head++];
            const currentDepth = nodeDepths.get(currentId);

            // Find children of the current node and assign their depth
            data.links.forEach(link => {
                const sourceId = typeof link.source === 'object' ? link.source.id : link.source;
                const targetId = typeof link.target === 'object' ? link.target.id : link.target;

                if (sourceId === currentId && link.type === 'parent_child') {
                    const newDepth = currentDepth + 1;
                    if (!processedForDepth.has(targetId) || newDepth > nodeDepths.get(targetId)) {
                        nodeDepths.set(targetId, newDepth);
                        if (!processedForDepth.has(targetId)) {
                            queue.push(targetId);
                            processedForDepth.add(targetId);
                        }
                    }
                }

                // Second pass: Assign same depth to spouses
                data.links.forEach(link => {
                    if (link.type === 'spouse') {
                        const sourceId = typeof link.source === 'object' ? link.source.id : link.source;
                        const targetId = typeof link.target === 'object' ? link.target.id : link.target;

                        const sourceDepth = nodeDepths.get(sourceId);
                        const targetDepth = nodeDepths.get(targetId);

                        // If only one spouse has a depth, assign it to the other
                        if (sourceDepth !== undefined && targetDepth === undefined) {
                            nodeDepths.set(targetId, sourceDepth);
                        } else if (targetDepth !== undefined && sourceDepth === undefined) {
                            nodeDepths.set(sourceId, targetDepth);
                        } else if (sourceDepth !== undefined && targetDepth !== undefined && sourceDepth !==
                            targetDepth) {
                            // Optional: If both have depth but not equal, unify them
                            const avgDepth = Math.round((sourceDepth + targetDepth) / 2);
                            nodeDepths.set(sourceId, avgDepth);
                            nodeDepths.set(targetId, avgDepth);
                        }
                    }
                });
            });
        }

        // Assign calculated depth to each node object
        data.nodes.forEach(node => {
            node.depth = nodeDepths.get(node.id) || 0;
            node.fy = node.depth * 150 + 50; // lock vertical position by depth
        });

        // --- Define the force simulation ---
        const simulation = d3.forceSimulation(data.nodes)
            .force("link", d3.forceLink(data.links)
                .id(d => d.id)
                .distance(120) // Increased distance for better vertical separation
                .strength(0.7)
            )
            .force("charge", d3.forceManyBody().strength(-300)) // Repel nodes
            .force("center", d3.forceCenter(width / 2, height / 2)) // Center the graph
            .force("collision", d3.forceCollide().radius(30)) // Prevent node overlap
            /* .force("y", d3.forceY().strength(0.02).y(d => d.depth * 150 +
                50)) // Force nodes to vertical layers based on depth */
            // Added: Force X to align parents horizontally and children below
            .force("x", d3.forceX().strength(0.02).x(d => {
                // Find parent(s) in the current graph data
                const parentLinks = data.links.filter(link => link.target.id === d.id && link.type ===
                    'parent_child');
                if (parentLinks.length > 0) {
                    // Average X of parents, or simply use the first parent's X
                    const parentX = d3.mean(parentLinks, link => link.source.x);
                    return parentX || width / 2; // If no parent X, center it
                }
                return width / 2; // For root nodes or those without parents, center horizontally
            }));


        // Create links (lines)
        const link = g.append("g")
            .attr("class", "links")
            .selectAll("line")
            .data(data.links)
            .enter().append("line")
            .attr("class", d => `link ${d.type}-link`); // Add class based on link type

        // Create nodes (groups containing circle, image, and text)
        const node = g.append("g")
            .attr("class", "nodes")
            .selectAll("g")
            .data(data.nodes)
            .enter().append("g")
            .call(d3.drag() // Add drag functionality to nodes
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended)
            );

        // Append Circle for border/background color (if no avatar)
        node.append("circle")
            .attr("r", 25) // Radius of the circle
            .attr("fill", d => d.sex === 'M' ? "#3498db" : "#e91e63") // Blue for Male, Pink for Female
            .style("display", d => d.avatar ? "none" : null); // Hide if avatar exists

        // Append Image for avatar
        node.append("image")
            .attr("xlink:href", d => d.avatar || '') // Use avatar URL if available
            .attr("x", -25) // Center image horizontally
            .attr("y", -25) // Center image vertically
            .attr("width", 50) // Width of image (2 * radius)
            .attr("height", 50) // Height of image (2 * radius)
            .attr("clip-path", d => d.avatar ? `url(#clip-${d.id})` : null) // Clip image to circle shape
            .style("display", d => d.avatar ? null : "none"); // Show only if avatar exists

        // Add a visible border circle on top of image/color
        node.append("circle")
            .attr("r", 25)
            .attr("fill", "none")
            .attr("stroke", d => d.sex === 'M' ? "#2980b9" : "#c0392b") // Slightly darker border
            .attr("stroke-width", 2);

        // Add event listeners for tooltip
        node.on("mouseover", function(event, d) {
                tooltip.style("opacity", 1)
                    .html(`<strong>${d.name}</strong><br>
                           Sex: ${d.sex}`)
                    .style("left", (event.pageX - 300) + "px")
                    .style("top", (event.pageY - 28) + "px");
            })
            .on("mouseout", function() {
                tooltip.style("opacity", 0);
            });
        // node.on("mouseover", function(event, d) {
        //         tooltip.style("opacity", 1)
        //             .html(`<strong>${d.name}</strong><br>
        //                Sex: ${d.sex}<br>
        //                DOB: ${d.dob || 'N/A'}<br>
        //                DOD: ${d.dod || 'N/A'}`)
        //             .style("left", (event.pageX + 10) + "px")
        //             .style("top", (event.pageY - 28) + "px");
        //     })
        //     .on("mouseout", function() {
        //         tooltip.style("opacity", 0);
        //     });

        node.append("text")
            .attr("dy", "0.35em") // Vertically center text
            .text(d => d.name)
            .attr("fill", "#000") // Text color set to black for better contrast on colored circles
            .attr("y", 38)
            .attr("x", -60); // Position name text below the circle

        // Update positions of nodes and links on each simulation tick
        simulation.on("tick", () => {
            link
                .attr("x1", d => d.source.x)
                .attr("y1", d => d.source.y)
                .attr("x2", d => d.target.x)
                .attr("y2", d => d.target.y);

            node
                .attr("transform", d => `translate(${d.x},${d.y})`);
        });

        // Drag functions
        function dragstarted(event, d) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            d.fx = d.x;
            // d.fy = d.y;
        }

        function dragged(event, d) {
            d.fx = event.x;
            // d.fy = event.y;
        }

        function dragended(event, d) {
            if (!event.active) simulation.alphaTarget(0);
            // d.fx = null; // Release fixed position
            // d.fy = null; // Release fixed position
        }

        // Zoom and Pan functionality
        const zoom = d3.zoom()
            .scaleExtent([0.1, 8]) // Min and max zoom level
            .on("zoom", (event) => {
                g.attr("transform", event.transform); // Apply zoom/pan transformation to the graph group
            });

        svg.call(zoom); // Apply zoom behavior to the SVG

        // Initial zoom to fit content (optional, can be removed if you prefer manual zoom)
        setTimeout(() => {
            const bounds = g.node().getBBox();
            const fullWidth = bounds.width;
            const fullHeight = bounds.height;
            const scale = Math.min(width / fullWidth, height / fullHeight) * 0.8; // 80% of fit
            const translateX = width / 2 - scale * (bounds.x + bounds.width / 2);
            const translateY = height / 2 - scale * (bounds.y + bounds.height / 2);

            svg.transition().duration(750).call(
                zoom.transform,
                d3.zoomIdentity.translate(translateX, translateY).scale(scale)
            );
        }, 500); // Give simulation a moment to settle
    </script>
</body>

</html>
