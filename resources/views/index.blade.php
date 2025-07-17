<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Hierarchy Tree</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        /* Dark mode styles */
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #111827;
            overflow: hidden;
        }

        .chart-container {
            width: 100%;
            height: 90vh;
            max-width: 1200px;
            background-color: #1f2937;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            position: relative;
        }

        .node circle {
            stroke: #e5e7eb;
            stroke-width: 1.5px;
            cursor: pointer;
        }

        .node text {
            font-size: 8px;
            fill: #f3f4f6;
            text-anchor: middle;
            pointer-events: none;
        }

        .link {
            stroke: #9ca3af;
            stroke-opacity: 0.6;
            stroke-width: 1.5px;
        }

        .link.spouse-link {
            stroke: #f87171;
            stroke-dasharray: 5 5;
        }

        .link.parent-child-link {
            stroke: #4ade80;
        }

        .tooltip {
            position: absolute;
            background-color: rgba(31, 41, 55, 0.9);
            color: #f9fafb;
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
        const graphData = @json($graphData);
        const data = JSON.parse(graphData);

        const width = document.querySelector('.chart-container').offsetWidth;
        const height = document.querySelector('.chart-container').offsetHeight;

        const svg = d3.select("#family-tree-svg")
            .attr("viewBox", `0 0 ${width} ${height}`);

        const g = svg.append("g");
        const tooltip = d3.select("#tooltip");

        g.append("defs").selectAll("clipPath")
            .data(data.nodes)
            .enter().append("clipPath")
            .attr("id", d => `clip-${d.id}`)
            .append("circle")
            .attr("r", 25);

        const nodeDepths = new Map();
        const queue = [];
        const processedForDepth = new Set();

        data.nodes.forEach(node => {
            const isChildOfAny = data.links.some(link => link.target.id === node.id && link.type ===
                'parent_child');
            if (!isChildOfAny) {
                nodeDepths.set(node.id, 0);
                queue.push(node.id);
                processedForDepth.add(node.id);
            }
        });

        let head = 0;
        while (head < queue.length) {
            const currentId = queue[head++];
            const currentDepth = nodeDepths.get(currentId);

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
            });
        }

        // Step 2: Normalize spouse depths AFTER BFS
        data.links.forEach(link => {
            if (link.type === 'spouse') {
                const sourceId = typeof link.source === 'object' ? link.source.id : link.source;
                const targetId = typeof link.target === 'object' ? link.target.id : link.target;

                const sourceDepth = nodeDepths.get(sourceId);
                const targetDepth = nodeDepths.get(targetId);

                if (sourceDepth !== undefined && targetDepth === undefined) {
                    nodeDepths.set(targetId, sourceDepth);
                } else if (targetDepth !== undefined && sourceDepth === undefined) {
                    nodeDepths.set(sourceId, targetDepth);
                } else if (sourceDepth !== undefined && targetDepth !== undefined && sourceDepth !== targetDepth) {
                    const avgDepth = Math.round((sourceDepth + targetDepth) / 2);
                    nodeDepths.set(sourceId, avgDepth);
                    nodeDepths.set(targetId, avgDepth);
                }
            }
        });

        data.nodes.forEach(node => {
            node.depth = nodeDepths.get(node.id) || 0;
            node.fy = node.depth * 150 + 50;
        });

        const simulation = d3.forceSimulation(data.nodes)
            .force("link", d3.forceLink(data.links).id(d => d.id).distance(120).strength(0.7))
            .force("charge", d3.forceManyBody().strength(-300))
            .force("center", d3.forceCenter(width / 2, height / 2))
            .force("collision", d3.forceCollide().radius(30))
            .force("x", d3.forceX().strength(0.02).x(d => {
                const parentLinks = data.links.filter(link => link.target.id === d.id && link.type ===
                    'parent_child');
                if (parentLinks.length > 0) {
                    const parentX = d3.mean(parentLinks, link => link.source.x);
                    return parentX || width / 2;
                }
                return width / 2;
            }))
            .force("y", d3.forceY().strength(1).y(d => d.depth * 150 + 50));

        const link = g.append("g")
            .attr("class", "links")
            .selectAll("line")
            .data(data.links)
            .enter().append("line")
            .attr("class", d => `link ${d.type}-link`);

        const node = g.append("g")
            .attr("class", "nodes")
            .selectAll("g")
            .data(data.nodes)
            .enter().append("g")
            .call(d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended)
            );

        node.append("circle")
            .attr("r", 25)
            .attr("fill", d =>
                d.sex === 'Male' ? "#3498db" :
                d.sex === 'Female' ? "#e91e63" :
                "#6A727F"
            )
            .style("display", d => d.avatar ? "none" : null);

        node.append("image")
            .attr("xlink:href", d => d.avatar || '')
            .attr("x", -25)
            .attr("y", -25)
            .attr("width", 50)
            .attr("height", 50)
            .attr("clip-path", d => d.avatar ? `url(#clip-${d.id})` : null)
            .style("display", d => d.avatar ? null : "none");

        node.append("circle")
            .attr("r", 25)
            .attr("fill", "none")
            .attr("stroke", d =>
                d.sex === 'Male' ? "#2980b9" :
                d.sex === 'Female' ? "#c0392b" :
                "#ffffff"
            )
            .attr("stroke-width", 2);

        node.on("mouseover", function(event, d) {
                tooltip.style("opacity", 1)
                    .html(`<strong>${d.name}</strong><br>Sex: ${d.sex}`)
                    .style("left", (event.pageX - 300) + "px")
                    .style("top", (event.pageY - 28) + "px");
            })
            .on("mouseout", function() {
                tooltip.style("opacity", 0);
            });

        node.append("text")
            .attr("dy", "0.35em")
            .text(d => d.name)
            .attr("fill", "#f3f4f6")
            .attr("y", 30)
            .attr("x", -15)
            .attr("style", "font-size: 6px");

        simulation.on("tick", () => {
            link
                .attr("x1", d => d.source.x)
                .attr("y1", d => d.source.y)
                .attr("x2", d => d.target.x)
                .attr("y2", d => d.target.y);

            node
                .attr("transform", d => `translate(${d.x},${d.y})`);
        });

        function dragstarted(event, d) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            d.fx = d.x;
        }

        function dragged(event, d) {
            d.fx = event.x;
        }

        function dragended(event, d) {
            if (!event.active) simulation.alphaTarget(0);
        }

        const zoom = d3.zoom()
            .scaleExtent([0.1, 8])
            .on("zoom", (event) => {
                g.attr("transform", event.transform);
            });

        svg.call(zoom);

        setTimeout(() => {
            const bounds = g.node().getBBox();
            const fullWidth = bounds.width;
            const fullHeight = bounds.height;
            const scale = Math.min(width / fullWidth, height / fullHeight) * 0.8;
            const translateX = width / 2 - scale * (bounds.x + bounds.width / 2);
            const translateY = height / 2 - scale * (bounds.y + bounds.height / 2);

            svg.transition().duration(750).call(
                zoom.transform,
                d3.zoomIdentity.translate(translateX, translateY).scale(scale)
            );
        }, 500);
    </script>
</body>

</html>
