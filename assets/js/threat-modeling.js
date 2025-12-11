class ThreatModelingTool {
    constructor() {
        this.systemData = {
            name: '',
            type: 'web_application',
            analysis_scope: 'comprehensive',
            components: [],
            data_flows: [],
            methodologies: ['stride', 'dread', 'mitre', 'ai_analysis'], // All methodologies
            frameworks: ['owasp', 'mitre', 'cwe', 'nist', 'cis', 'iso27001'] // All frameworks
        };
        this.selectedComponent = null;
        this.connectionSource = null;
        this.diagramState = 'idle';
        this.isDragging = false;
        this.dragOffset = { x: 0, y: 0 };
        this.draggedComponent = null;
        this.tempConnection = null;
        
        // Initialize after DOM is fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initEventListeners();
                this.initComponentLibrary();
                this.initComponentPropertiesPanel();
                this.initReadMoreButtons();
            });
        } else {
            this.initEventListeners();
            this.initComponentLibrary();
            this.initComponentPropertiesPanel();
            this.initReadMoreButtons();
        }
    }

    initEventListeners() {
    // System definition
        const systemNameInput = document.getElementById('system-name');
        const systemTypeSelect = document.getElementById('system-type');
        const analysisScopeSelect = document.getElementById('analysis-scope');
        
        if (systemNameInput) {
            systemNameInput.addEventListener('input', (e) => {
                this.systemData.name = e.target.value;
            });
        }

        if (systemTypeSelect) {
            systemTypeSelect.addEventListener('change', (e) => {
                this.systemData.type = e.target.value;
            });
        }

        if (analysisScopeSelect) {
            analysisScopeSelect.addEventListener('change', (e) => {
                this.systemData.analysis_scope = e.target.value;
            });
        }

        // Analysis buttons
        const analyzeBtn = document.getElementById('analyze-threats');
        if (analyzeBtn) {
            analyzeBtn.addEventListener('click', () => {
                this.analyzeThreats();
            });
        }

        // Clear canvas
        const clearCanvasBtn = document.getElementById('clear-canvas');
        if (clearCanvasBtn) {
            clearCanvasBtn.addEventListener('click', () => {
                this.clearCanvas();
            });
        }

        // Connection mode
        const connectionModeBtn = document.getElementById('connection-mode');
        if (connectionModeBtn) {
            connectionModeBtn.addEventListener('click', () => {
                this.toggleConnectionMode();
            });
        }

        // Auto layout
        const autoLayoutBtn = document.getElementById('auto-layout');
        if (autoLayoutBtn) {
            autoLayoutBtn.addEventListener('click', () => {
                this.autoLayout();
            });
        }

        // Save draft
        const saveDraftBtn = document.getElementById('save-draft');
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', () => {
                this.saveDraft();
            });
        }

        // Validate model
        const validateModelBtn = document.getElementById('validate-model');
        if (validateModelBtn) {
            validateModelBtn.addEventListener('click', () => {
                this.validateModel();
            });
        }

        // Generate report
        const generateReportBtn = document.getElementById('generate-report');
        if (generateReportBtn) {
            generateReportBtn.addEventListener('click', () => {
                this.generateReport();
            });
        }

        // Load previous analyses
        document.querySelectorAll('.load-analysis').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const uuid = e.target.getAttribute('data-uuid');
                this.loadAnalysis(uuid);
            });
        });

        // Tab switching
        const tabHeaders = document.querySelectorAll('.tab-header');
        if (tabHeaders.length > 0) {
            tabHeaders.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    this.switchTab(e.target.getAttribute('data-tab'));
                });
            });
        }

        // Component search
        const componentSearch = document.getElementById('component-search');
        if (componentSearch) {
            componentSearch.addEventListener('input', (e) => {
                this.filterComponents(e.target.value);
            });
        }

        // Methodologies checkboxes
        this.initMethodologiesListeners();
        
        // Frameworks checkboxes
        this.initFrameworksListeners();

        // Canvas mouse events for temporary connections
        const canvas = document.getElementById('flow-canvas');
        if (canvas) {
            canvas.addEventListener('mousemove', this.handleCanvasMouseMove.bind(this));
            canvas.addEventListener('click', this.handleCanvasClick.bind(this));
        }
    }

    initFrameworksListeners() {
        const frameworkCheckboxes = document.querySelectorAll('input[name="frameworks"]');
        
        frameworkCheckboxes.forEach(checkbox => {
            // Set initial state based on default systemData
            checkbox.checked = this.systemData.frameworks.includes(checkbox.value);
            
            checkbox.addEventListener('change', (e) => {
                if (e.target.checked) {
                    if (!this.systemData.frameworks.includes(checkbox.value)) {
                        this.systemData.frameworks.push(checkbox.value);
                    }
                } else {
                    this.systemData.frameworks = this.systemData.frameworks.filter(
                        f => f !== checkbox.value
                    );
                }
                //console.log('Updated frameworks:', this.systemData.frameworks);
            });
        });
    }

    // Add these new methods to capture all methodologies and frameworks
    initMethodologiesListeners() {
        const methodologyCheckboxes = [
            { id: 'analyze-stride', value: 'stride' },
            { id: 'analyze-dread', value: 'dread' },
            { id: 'analyze-mitre', value: 'mitre' },
            { id: 'analyze-ai', value: 'ai_analysis' }
        ];

        methodologyCheckboxes.forEach(methodology => {
            const checkbox = document.getElementById(methodology.id);
            if (checkbox) {
                // Set initial state based on default systemData
                checkbox.checked = this.systemData.methodologies.includes(methodology.value);
                
                checkbox.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        if (!this.systemData.methodologies.includes(methodology.value)) {
                            this.systemData.methodologies.push(methodology.value);
                        }
                    } else {
                        this.systemData.methodologies = this.systemData.methodologies.filter(
                            m => m !== methodology.value
                        );
                    }
                    console.log('Updated methodologies:', this.systemData.methodologies);
                });
            }
        });
    }


    switchTab(tabName) {
        // Safely update tab headers
        const tabHeaders = document.querySelectorAll('.tab-header');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        if (tabHeaders.length === 0 || tabPanes.length === 0) {
            return; // No tabs found, exit early
        }
        
        // Update tab headers
        tabHeaders.forEach(tab => {
            if (tab.classList) {
                tab.classList.remove('active');
            }
        });
        
        const activeTabHeader = document.querySelector(`[data-tab="${tabName}"]`);
        if (activeTabHeader && activeTabHeader.classList) {
            activeTabHeader.classList.add('active');
        }

        // Update tab content
        tabPanes.forEach(pane => {
            if (pane.classList) {
                pane.classList.remove('active');
            }
        });
        
        const activeTabPane = document.getElementById(tabName);
        if (activeTabPane && activeTabPane.classList) {
            activeTabPane.classList.add('active');
        }
    }

    initComponentLibrary() {
        const componentLibrary = {
            user_entities: [
                { type: 'user', name: 'End User', icon: 'user', category: 'user' },
                { type: 'admin', name: 'Administrator', icon: 'user-cog', category: 'user' },
                { type: 'service_account', name: 'Service Account', icon: 'user-tie', category: 'user' },
                { type: 'customer', name: 'Customer', icon: 'users', category: 'user' }
            ],
            infrastructure: [
                { type: 'web_server', name: 'Web Server', icon: 'server', category: 'infrastructure' },
                { type: 'application_server', name: 'App Server', icon: 'layer-group', category: 'infrastructure' },
                { type: 'database', name: 'Database', icon: 'database', category: 'infrastructure' },
                { type: 'load_balancer', name: 'Load Balancer', icon: 'balance-scale', category: 'infrastructure' },
                { type: 'firewall', name: 'Firewall', icon: 'fire', category: 'infrastructure' },
                { type: 'proxy', name: 'Proxy Server', icon: 'exchange-alt', category: 'infrastructure' },
                { type: 'laptop', name: 'Laptop', icon: 'laptop', category: 'infrastructure' },
                { type: 'computer', name: 'Computer', icon: 'computer', category: 'infrastructure' },
                { type: 'wifi', name: 'Wi-Fi', icon: 'wifi', category: 'infrastructure' }
            ],
            cloud_services: [
                { type: 'aws_lambda', name: 'AWS Lambda', icon: 'bolt', category: 'cloud' },
                { type: 'aws_s3', name: 'AWS S3', icon: 'archive', category: 'cloud' },
                { type: 'aws_ec2', name: 'AWS EC2', icon: 'server', category: 'cloud' },
                { type: 'azure_function', name: 'Azure Function', icon: 'cloud', category: 'cloud' },
                { type: 'google_cloud', name: 'Google Cloud', icon: 'cloud', category: 'cloud' },
                { type: 'kubernetes', name: 'Kubernetes', icon: 'docker', category: 'cloud' },
                { type: 'container', name: 'Container', icon: 'box', category: 'cloud' }
            ],
            mobile: [
                { type: 'mobile', name: 'Mobile', icon: 'mobile-alt', category: 'mobile' },
                { type: 'android', name: 'Android', icon: 'android', category: 'mobile' },
                { type: 'ios', name: 'iOS', icon: 'apple', category: 'mobile' },
                { type: 'tablet', name: 'Tablet', icon: 'tablet', category: 'mobile' }
            ],
            iot_devices: [
                { type: 'sensor', name: 'IoT Sensor', icon: 'thermometer-half', category: 'iot' },
                { type: 'gateway', name: 'IoT Gateway', icon: 'broadcast-tower', category: 'iot' },
                { type: 'controller', name: 'Controller', icon: 'gamepad', category: 'iot' }
            ],
            security_components: [
                { type: 'waf', name: 'WAF', icon: 'shield-alt', category: 'security' },
                { type: 'ids_ips', name: 'IDS/IPS', icon: 'eye', category: 'security' },
                { type: 'vpn', name: 'VPN Gateway', icon: 'user-shield', category: 'security' },
                { type: 'auth_server', name: 'Auth Server', icon: 'key', category: 'security' },
                { type: 'certificate_authority', name: 'CA', icon: 'certificate', category: 'security' }
            ],
            external_systems: [
                { type: 'api_gateway', name: 'API Gateway', icon: 'code-branch', category: 'external' },
                { type: 'third_party_api', name: '3rd Party API', icon: 'plug', category: 'external' },
                { type: 'payment_gateway', name: 'Payment Gateway', icon: 'credit-card', category: 'external' },
                { type: 'cdn', name: 'CDN', icon: 'globe', category: 'external' },
                { type: 'legacy_system', name: 'Legacy System', icon: 'history', category: 'external' }
            ],
            data_storage: [
                { type: 'file_storage', name: 'File Storage', icon: 'file', category: 'storage' },
                { type: 'object_storage', name: 'Object Storage', icon: 'cube', category: 'storage' },
                { type: 'block_storage', name: 'Block Storage', icon: 'hdd', category: 'storage' },
                { type: 'data_warehouse', name: 'Data Warehouse', icon: 'warehouse', category: 'storage' },
                { type: 'cache', name: 'Cache', icon: 'bolt', category: 'storage' }
            ]
        };

        this.renderComponentLibrary(componentLibrary);
    }

    renderComponentLibrary(library) {
        const sidebar = document.querySelector('.components-sidebar .components-list');
        sidebar.innerHTML = '';

        Object.entries(library).forEach(([category, components]) => {
            const categorySection = document.createElement('div');
            categorySection.className = 'component-category';
            categorySection.innerHTML = `
                <h4>${this.formatCategoryName(category)}</h4>
                <div class="category-components">
                    ${components.map(comp => `
                        <div class="component-item" data-type="${comp.type}" draggable="true">
                            <i class="${this.getComponentIcon(comp.type)}"></i>
                            <span>${comp.name}</span>
                        </div>
                    `).join('')}
                </div>
            `;
            sidebar.appendChild(categorySection);
        });

        // Re-initialize event listeners for new components
        this.initComponentManagement();
    }

    initComponentManagement() {
        const components = document.querySelectorAll('.component-item');
        components.forEach(component => {
            component.setAttribute('draggable', 'true');
            component.addEventListener('dragstart', this.handleDragStart.bind(this));
        });

        const canvas = document.getElementById('flow-canvas');
        canvas.addEventListener('dragover', this.handleDragOver.bind(this));
        canvas.addEventListener('drop', this.handleDrop.bind(this));
        canvas.addEventListener('click', this.handleCanvasClick.bind(this));
        
        // Enhanced mouse events for component movement
        canvas.addEventListener('mousedown', this.handleCanvasMouseDown.bind(this));
        canvas.addEventListener('mousemove', this.handleCanvasMouseMove.bind(this));
        canvas.addEventListener('mouseup', this.handleCanvasMouseUp.bind(this));
        canvas.addEventListener('mouseleave', this.handleCanvasMouseUp.bind(this));
    }

    handleDragStart(e) {
        const componentType = e.target.getAttribute('data-type');
        e.dataTransfer.setData('text/plain', componentType);
        e.dataTransfer.effectAllowed = 'copy';
    }

    handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        e.currentTarget.classList.add('drag-over');
    }

    handleDrop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('drag-over');
        
        const componentType = e.dataTransfer.getData('text/plain');
        this.addComponentToCanvas(componentType, e.offsetX, e.offsetY);
    }

    handleCanvasClick(e) {
        // If clicking on canvas (not a component) while in connection mode, cancel connection
        if (this.diagramState === 'connecting' && !e.target.closest('.canvas-component')) {
            this.cancelConnection();
        }
        
        // If clicking on a component, show properties (unless in connection mode)
        if (this.diagramState === 'idle' && e.target.closest('.canvas-component')) {
            const componentEl = e.target.closest('.canvas-component');
            this.showComponentProperties(componentEl);
        } else {
            this.hidePropertiesPanel();
        }
    }

    handleCanvasMouseDown(e) {
        if (e.target.closest('.canvas-component')) {
            const componentEl = e.target.closest('.canvas-component');
            const rect = componentEl.getBoundingClientRect();
            const canvasRect = e.currentTarget.getBoundingClientRect();
            
            this.isDragging = true;
            this.draggedComponent = componentEl;
            this.dragOffset = {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
            
            componentEl.classList.add('dragging');
            e.preventDefault();
        }
    }

    handleCanvasMouseMove(e) {
        // Handle component dragging
        if (this.isDragging && this.draggedComponent) {
            const canvas = document.getElementById('flow-canvas');
            const canvasRect = canvas.getBoundingClientRect();
            
            const x = e.clientX - canvasRect.left - this.dragOffset.x;
            const y = e.clientY - canvasRect.top - this.dragOffset.y;
            
            // Constrain to canvas boundaries
            const constrainedX = Math.max(0, Math.min(x, canvasRect.width - this.draggedComponent.offsetWidth));
            const constrainedY = Math.max(0, Math.min(y, canvasRect.height - this.draggedComponent.offsetHeight));
            
            this.draggedComponent.style.left = constrainedX + 'px';
            this.draggedComponent.style.top = constrainedY + 'px';
            
            // Update component position in system data
            const component = this.findComponentById(this.draggedComponent.id);
            if (component) {
                component.position = { x: constrainedX, y: constrainedY };
            }
            
            // Update connections for this component
            this.updateConnectionsForComponent(this.draggedComponent.id);
        }
    }

    handleCanvasMouseUp(e) {
        if (this.isDragging && this.draggedComponent) {
            this.draggedComponent.classList.remove('dragging');
            this.isDragging = false;
            this.draggedComponent = null;
        }
    }

    updateConnectionsForComponent(componentId) {
        // Find all connections related to this component
        const connections = this.systemData.data_flows.filter(
            flow => flow.sourceId === componentId || flow.destinationId === componentId
        );
        
        // Re-render these connections
        connections.forEach(connection => {
            const existingConn = document.getElementById(connection.id);
            if (existingConn) {
                existingConn.remove();
            }
            this.renderConnection(connection);
        });
    }

    addComponentToCanvas(type, x, y) {
        const componentName = prompt(`Enter name for ${this.getComponentDisplayName(type)}:`, this.generateComponentName(type));
        if (!componentName) return;

        const component = {
            id: 'comp-' + Date.now(),
            name: componentName,
            type: type,
            position: { x: x - 60, y: y - 40 }, // Center the component
            sensitivity: 'medium',
            description: ''
        };

        this.systemData.components.push(component);
        this.renderComponentOnCanvas(component);
        
        // Remove placeholder if it exists
        const placeholder = document.querySelector('.canvas-placeholder');
        if (placeholder) {
            placeholder.remove();
        }
    }

    generateComponentName(type) {
        const baseName = this.getComponentDisplayName(type);
        const count = this.systemData.components.filter(c => c.type === type).length + 1;
        return count === 1 ? baseName : `${baseName} ${count}`;
    }

    renderComponentOnCanvas(component) {
        const canvas = document.getElementById('flow-canvas');
        const componentEl = document.createElement('div');
        componentEl.className = `canvas-component ${component.type} ${component.sensitivity}`;
        componentEl.id = component.id;
        componentEl.style.left = `${component.position.x}px`;
        componentEl.style.top = `${component.position.y}px`;
        componentEl.setAttribute('draggable', 'true');
        
        componentEl.innerHTML = `
            <div class="component-icon">  
             <i class="fas fa-${this.getComponentIcon(component.type)}"></i>
            </div>
            <div class="component-name">${component.name}</div>
            <div class="component-sensitivity sensitivity-${component.sensitivity}">
                ${component.sensitivity.toUpperCase()}
            </div>
        `;
        
        // Make component draggable
        componentEl.addEventListener('dragstart', (e) => this.handleComponentDragStart(e, component));
        componentEl.addEventListener('click', (e) => {
            e.stopPropagation();
            this.showComponentProperties(componentEl);
        });
        
        // Add connection points
        this.addConnectionPoints(componentEl);
        
        canvas.appendChild(componentEl);
    }

    getComponentIcon(type) {
        const iconMap = {
            'user': { icon: 'user', style: 'fas' },
            'admin': { icon: 'user-cog', style: 'fas' },
            'service_account': { icon: 'user-tie', style: 'fas' },
            'customer': { icon: 'users', style: 'fas' },
            'web_server': { icon: 'server', style: 'fas' },
            'application_server': { icon: 'layer-group', style: 'fas' },
            'database': { icon: 'database', style: 'fas' },
            'load_balancer': { icon: 'balance-scale', style: 'fas' },
            'firewall': { icon: 'fire', style: 'fas' },
            'proxy': { icon: 'exchange-alt', style: 'fas' },
            'aws_lambda': { icon: 'bolt', style: 'fas' },
            'aws_s3': { icon: 'archive', style: 'fas' },
            'aws_ec2': { icon: 'server', style: 'fas' },
            'azure_function': { icon: 'cloud', style: 'fas' },
            'google_cloud': { icon: 'cloud', style: 'fas' },
            'kubernetes': { icon: 'docker', style: 'fa-brands' },
            'container': { icon: 'box', style: 'fas' },
            'sensor': { icon: 'thermometer-half', style: 'fas' },
            'gateway': { icon: 'broadcast-tower', style: 'fas' },
            'controller': { icon: 'gamepad', style: 'fas' },
            'waf': { icon: 'shield-alt', style: 'fas' },
            'ids_ips': { icon: 'eye', style: 'fas' },
            'vpn': { icon: 'user-shield', style: 'fas' },
            'auth_server': { icon: 'key', style: 'fas' },
            'certificate_authority': { icon: 'certificate', style: 'fas' },
            'api_gateway': { icon: 'code-branch', style: 'fas' },
            'third_party_api': { icon: 'plug', style: 'fas' },
            'payment_gateway': { icon: 'credit-card', style: 'fas' },
            'cdn': { icon: 'globe', style: 'fas' },
            'legacy_system': { icon: 'history', style: 'fas' },
            'file_storage': { icon: 'file', style: 'fas' },
            'object_storage': { icon: 'cube', style: 'fas' },
            'block_storage': { icon: 'hdd', style: 'fas' },
            'data_warehouse': { icon: 'warehouse', style: 'fas' },
            'cache': { icon: 'bolt', style: 'fas' },
            'laptop': { icon: 'laptop', style: 'fas' },
            'computer': { icon: 'computer', style: 'fas' },
            'wifi': { icon: 'wifi', style: 'fas' },
            'ios': { icon: 'apple', style: 'fa-brands' },
            'android': { icon: 'android', style: 'fa-brands' },
            'mobile': { icon: 'mobile', style: 'fas' },
            'tablet': { icon: 'tablet', style: 'fas' }
        };
        
        const iconData = iconMap[type] || { icon: 'cube', style: 'fas' || 'fa-brands' };
        return `${iconData.style} fa-${iconData.icon}`;
    }

    getComponentDisplayName(type) {
        const nameMap = {
            'user': 'End User',
            'admin': 'Administrator',
            'service_account': 'Service Account',
            'customer': 'Customer',
            'web_server': 'Web Server',
            'application_server': 'Application Server',
            'database': 'Database',
            'load_balancer': 'Load Balancer',
            'firewall': 'Firewall',
            'proxy': 'Proxy Server',
            'aws_lambda': 'AWS Lambda',
            'aws_s3': 'AWS S3',
            'aws_ec2': 'AWS EC2',
            'azure_function': 'Azure Function',
            'google_cloud': 'Google Cloud',
            'kubernetes': 'Kubernetes',
            'container': 'Container',
            'sensor': 'IoT Sensor',
            'smart_device': 'Smart Device',
            'gateway': 'IoT Gateway',
            'controller': 'Controller',
            'waf': 'WAF',
            'ids_ips': 'IDS/IPS',
            'vpn': 'VPN Gateway',
            'auth_server': 'Auth Server',
            'certificate_authority': 'Certificate Authority',
            'api_gateway': 'API Gateway',
            'third_party_api': '3rd Party API',
            'payment_gateway': 'Payment Gateway',
            'cdn': 'CDN',
            'legacy_system': 'Legacy System',
            'file_storage': 'File Storage',
            'object_storage': 'Object Storage',
            'block_storage': 'Block Storage',
            'data_warehouse': 'Data Warehouse',
            'cache': 'Cache',
            'laptop': 'Laptop',
            'computer': 'Computer',
            'wifi': 'Wi-Fi',
            'mobile': 'Mobile',
            'ios': 'iOS',
            'android': 'Android',
            'tablet': 'Tablet'
        };
        return nameMap[type] || type;
    }

    addConnectionPoints(componentEl) {
        const connectionPoints = [
            { position: 'top', x: '50%', y: '0%' },
            { position: 'right', x: '100%', y: '50%' },
            { position: 'bottom', x: '50%', y: '100%' },
            { position: 'left', x: '0%', y: '50%' }
        ];
        
        connectionPoints.forEach(point => {
            const pointEl = document.createElement('div');
            pointEl.className = `connection-point ${point.position}`;
            pointEl.style.left = point.x;
            pointEl.style.top = point.y;
            pointEl.style.transform = 'translate(-50%, -50%)';
            pointEl.innerHTML = '<i class="fas fa-circle"></i>';
            pointEl.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handleConnectionPointClick(componentEl, point.position);
            });
            pointEl.addEventListener('mouseenter', () => {
                pointEl.style.transform = 'translate(-50%, -50%) scale(1.2)';
            });
            pointEl.addEventListener('mouseleave', () => {
                pointEl.style.transform = 'translate(-50%, -50%) scale(1)';
            });
            componentEl.appendChild(pointEl);
        });
    }

    handleConnectionPointClick(sourceComponent, position) {
        if (this.diagramState === 'idle') {
            this.startConnection(sourceComponent, position);
        } else if (this.diagramState === 'connecting' && this.connectionSource) {
            this.completeConnection(sourceComponent, position);
        }
    }

    startConnection(sourceComponent, position) {
        this.diagramState = 'connecting';
        this.connectionSource = {
            component: sourceComponent,
            position: position,
            componentId: sourceComponent.id
        };
        
        sourceComponent.classList.add('connecting-source');
        document.getElementById('flow-canvas').classList.add('connection-mode');
        
        // Create temporary connection line
        this.createTemporaryConnection(sourceComponent, position);
        
        this.showNotification('Click on destination component to create connection', 'info');
    }

    createTemporaryConnection(sourceComponent, startPosition) {
        const canvas = document.getElementById('flow-canvas');
        
        // Remove existing temporary connection
        if (this.tempConnection) {
            this.tempConnection.remove();
        }

        this.tempConnection = document.createElement('div');
        this.tempConnection.className = 'temp-connection';
        this.tempConnection.id = 'temp-connection';
        
        const sourceRect = sourceComponent.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();
        
        const startPoint = this.getConnectionPoint(sourceRect, startPosition, canvasRect);
        
        this.tempConnection.innerHTML = `
            <svg class="connection-svg" width="100%" height="100%">
                <path id="temp-path" d="M ${startPoint.x} ${startPoint.y} L ${startPoint.x} ${startPoint.y}" 
                      class="connection-line temp" />
            </svg>
        `;
        
        canvas.appendChild(this.tempConnection);
        
        // Update temporary connection on mouse move
        this.updateTemporaryConnection = (e) => {
            const mouseX = e.clientX - canvasRect.left;
            const mouseY = e.clientY - canvasRect.top;
            
            const path = document.getElementById('temp-path');
            if (path) {
                path.setAttribute('d', `M ${startPoint.x} ${startPoint.y} L ${mouseX} ${mouseY}`);
            }
        };
        
        canvas.addEventListener('mousemove', this.updateTemporaryConnection);
    }

    completeConnection(destComponent, destPosition) {
        if (!this.connectionSource) return;
        
        const sourceComponent = this.connectionSource.component;
        
        if (sourceComponent === destComponent) {
            this.showNotification('Cannot connect component to itself', 'warning');
            this.cancelConnection();
            return;
        }

        const sourceComp = this.findComponentById(sourceComponent.id);
        const destComp = this.findComponentById(destComponent.id);
        
        if (sourceComp && destComp) {
            // Show protocol selection dialog
            this.showProtocolSelection(sourceComp, destComp, destPosition);
        } else {
            this.cancelConnection();
        }
    }

    showProtocolSelection(sourceComp, destComp, destPosition) {
        const protocols = [
            'HTTP', 'HTTPS', 'TCP', 'UDP', 'WebSocket', 'gRPC', 
            'FTP', 'SSH', 'SMTP', 'DNS', 'Database', 'Message Queue',
            'File System', 'RPC', 'MQTT', 'CoAP', 'AMQP', 'Custom', 'SSL', 
            'IPv4', 'IPv6', 'RDP', 'Bluetooth'
        ];
        
        const modal = document.createElement('div');
        modal.className = 'protocol-modal-tm';
        modal.innerHTML = `
            <div class="modal-content-tm">
                <div class="modal-header-tm">
                    <h4>Select Connection Protocol</h4>
                    <button class="btn-close">&times;</button>
                </div>
                <div class="modal-body-tm">
                    <p>Connecting <strong>${sourceComp.name}</strong> to <strong>${destComp.name}</strong></p>
                    <div class="protocol-grid">
                        ${protocols.map(protocol => `
                            <button class="protocol-option" data-protocol="${protocol}">
                                <i class="fas fa-${this.getProtocolIcon(protocol)}"></i>
                                <span>${protocol}</span>
                            </button>
                        `).join('')}
                    </div>
                    <div class="custom-protocol" style="display: none;">
                        <input type="text" id="custom-protocol-input" placeholder="Enter custom protocol name" class="property-input">
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add event listeners
        modal.querySelector('.btn-close').addEventListener('click', () => {
            modal.remove();
            this.cancelConnection();
        });
        
        modal.querySelectorAll('.protocol-option').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const protocol = e.currentTarget.getAttribute('data-protocol');
                if (protocol === 'Custom') {
                    // Show custom protocol input
                    modal.querySelector('.custom-protocol').style.display = 'block';
                    return;
                }
                this.createConnectionWithProtocol(sourceComp, destComp, destPosition, protocol);
                modal.remove();
            });
        });
        
        // Handle custom protocol
        const customInput = modal.querySelector('#custom-protocol-input');
        customInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const customProtocol = customInput.value.trim();
                if (customProtocol) {
                    this.createConnectionWithProtocol(sourceComp, destComp, destPosition, customProtocol);
                    modal.remove();
                }
            }
        });
        
        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
                this.cancelConnection();
            }
        });
    }

    getProtocolIcon(protocol) {
        const iconMap = {
            'HTTP': 'globe',
            'HTTPS': 'lock',
            'TCP': 'network-wired',
            'UDP': 'broadcast-tower',
            'WebSocket': 'plug',
            'gRPC': 'bolt',
            'FTP': 'file-upload',
            'SSH': 'terminal',
            'SMTP': 'envelope',
            'DNS': 'search',
            'Database': 'database',
            'Message Queue': 'list',
            'File System': 'hdd',
            'RPC': 'exchange-alt',
            'MQTT': 'satellite-dish',
            'CoAP': 'microchip',
            'AMQP': 'rabbit',
            'Custom': 'edit',
            'SSL': 'ssl',
            'IPv6': 'ipv6',
            'IPv4': 'ipv4',
            'Bluetooth': 'bluetooth-2',
            'RDP': 'rdp-connection'
        };
        return iconMap[protocol] || 'exchange-alt';
    }

    createConnectionWithProtocol(sourceComp, destComp, destPosition, protocol) {
        const connection = {
            id: 'conn-' + Date.now(),
            source: sourceComp.name,
            sourceId: sourceComp.id,
            sourcePosition: this.connectionSource.position,
            destination: destComp.name,
            destinationId: destComp.id,
            destinationPosition: destPosition,
            data_type: this.getDataTypeFromProtocol(protocol),
            protocol: protocol
        };
        
        this.systemData.data_flows.push(connection);
        this.renderConnection(connection);
        this.showNotification(`Connected ${sourceComp.name} to ${destComp.name} with ${protocol}`, 'success');
        
        this.cancelConnection();
    }

    getDataTypeFromProtocol(protocol) {
        const dataTypeMap = {
            'HTTP': 'web_traffic',
            'HTTPS': 'encrypted_web_traffic',
            'TCP': 'binary_data',
            'UDP': 'streaming_data',
            'WebSocket': 'real_time_data',
            'gRPC': 'rpc_data',
            'FTP': 'file_transfer',
            'SSH': 'encrypted_session',
            'SMTP': 'email',
            'DNS': 'dns_queries',
            'Database': 'database_queries',
            'Message Queue': 'messages',
            'File System': 'file_operations',
            'RPC': 'remote_calls',
            'MQTT': 'iot_messages',
            'CoAP': 'iot_data',
            'AMQP': 'message_broker',
            'SSL': 'secure_ssl',
            'IPv4': 'ipv4',
            'IPv6': 'ipv6',
            'Bluetooth': 'bluetooth',
            'RDP': 'rdp-connection'
        };
        return dataTypeMap[protocol] || 'generic';
    }

    cancelConnection() {
        this.diagramState = 'idle';
        
        // Remove temporary connection
        if (this.tempConnection) {
            this.tempConnection.remove();
            this.tempConnection = null;
        }
        
        // Remove event listener
        const canvas = document.getElementById('flow-canvas');
        if (this.updateTemporaryConnection) {
            canvas.removeEventListener('mousemove', this.updateTemporaryConnection);
            this.updateTemporaryConnection = null;
        }
        
        // Reset connection source
        if (this.connectionSource) {
            const sourceComponent = document.getElementById(this.connectionSource.componentId);
            if (sourceComponent) {
                sourceComponent.classList.remove('connecting-source');
            }
            this.connectionSource = null;
        }
        
        document.getElementById('flow-canvas').classList.remove('connection-mode');
    }

    renderConnection(connection) {
        const canvas = document.getElementById('flow-canvas');
        
        // Remove existing connection if it exists
        const existingConn = document.getElementById(connection.id);
        if (existingConn) {
            existingConn.remove();
        }

        const connectionEl = document.createElement('div');
        connectionEl.className = 'data-flow-connection';
        connectionEl.id = connection.id;
        
        const sourceEl = document.getElementById(connection.sourceId);
        const destEl = document.getElementById(connection.destinationId);
        
        if (sourceEl && destEl) {
            const pathData = this.calculateConnectionPath(
                sourceEl, 
                connection.sourcePosition, 
                destEl, 
                connection.destinationPosition
            );
            
            // Convert protocol to lowercase for CSS classes
            const protocolClass = connection.protocol.toLowerCase().replace(/\s+/g, '-');
            
            connectionEl.innerHTML = `
                <svg class="connection-svg" width="100%" height="100%">
                    <path d="${pathData.path}" class="connection-line ${protocolClass}" />
                    <circle cx="${pathData.endX}" cy="${pathData.endY}" r="4" class="connection-arrow ${protocolClass}" />
                </svg>
                <div class="connection-label ${protocolClass}" style="left: ${pathData.labelX}px; top: ${pathData.labelY}px">
                    ${connection.protocol}
                </div>
            `;
            
            // Add click handler to delete connection
            connectionEl.addEventListener('click', (e) => {
                e.stopPropagation();
                this.deleteConnection(connection.id);
            });
            
            // Add double-click handler to change protocol
            connectionEl.addEventListener('dblclick', (e) => {
                e.stopPropagation();
                this.changeConnectionProtocol(connection);
            });
            
            canvas.appendChild(connectionEl);
        }
    }

    changeConnectionProtocol(connection) {
        const sourceComp = this.findComponentById(connection.sourceId);
        const destComp = this.findComponentById(connection.destinationId);
        
        if (sourceComp && destComp) {
            // Delete the old connection
            this.deleteConnection(connection.id);
            
            // Create new connection with protocol selection
            this.connectionSource = {
                component: document.getElementById(connection.sourceId),
                position: connection.sourcePosition,
                componentId: connection.sourceId
            };
            
            this.showProtocolSelection(sourceComp, destComp, connection.destinationPosition);
        }
    }

    deleteConnection(connectionId) {
        if (confirm('Delete this connection?')) {
            // Remove from system data
            this.systemData.data_flows = this.systemData.data_flows.filter(
                flow => flow.id !== connectionId
            );
            
            // Remove from canvas
            const connectionEl = document.getElementById(connectionId);
            if (connectionEl) {
                connectionEl.remove();
            }
            
            this.showNotification('Connection deleted', 'success');
        }
    }

    calculateConnectionPath(sourceEl, sourcePos, destEl, destPos) {
        const sourceRect = sourceEl.getBoundingClientRect();
        const destRect = destEl.getBoundingClientRect();
        const canvasRect = document.getElementById('flow-canvas').getBoundingClientRect();
        
        const startPoint = this.getConnectionPoint(sourceRect, sourcePos, canvasRect);
        const endPoint = this.getConnectionPoint(destRect, destPos, canvasRect);
        
        // Calculate control points for curved line
        const dx = Math.abs(endPoint.x - startPoint.x);
        const dy = Math.abs(endPoint.y - startPoint.y);
        
        let controlPoint1, controlPoint2;
        
        // Determine curve direction based on relative positions
        if (Math.abs(dx) > Math.abs(dy)) {
            // Horizontal dominant
            const offsetX = dx * 0.3;
            controlPoint1 = { x: startPoint.x + offsetX, y: startPoint.y };
            controlPoint2 = { x: endPoint.x - offsetX, y: endPoint.y };
        } else {
            // Vertical dominant
            const offsetY = dy * 0.3;
            controlPoint1 = { x: startPoint.x, y: startPoint.y + offsetY };
            controlPoint2 = { x: endPoint.x, y: endPoint.y - offsetY };
        }
        
        // Calculate label position (midpoint of the curve)
        const labelX = (startPoint.x + endPoint.x) / 2;
        const labelY = (startPoint.y + endPoint.y) / 2;
        
        return {
            path: `M ${startPoint.x} ${startPoint.y} C ${controlPoint1.x} ${controlPoint1.y}, ${controlPoint2.x} ${controlPoint2.y}, ${endPoint.x} ${endPoint.y}`,
            endX: endPoint.x,
            endY: endPoint.y,
            labelX: labelX,
            labelY: labelY
        };
    }

    getConnectionPoint(rect, position, canvasRect) {
        const centerX = rect.left + rect.width / 2 - canvasRect.left;
        const centerY = rect.top + rect.height / 2 - canvasRect.top;
        
        switch(position) {
            case 'top':
                return {
                    x: centerX,
                    y: rect.top - canvasRect.top
                };
            case 'right':
                return {
                    x: rect.left + rect.width - canvasRect.left,
                    y: centerY
                };
            case 'bottom':
                return {
                    x: centerX,
                    y: rect.top + rect.height - canvasRect.top
                };
            case 'left':
                return {
                    x: rect.left - canvasRect.left,
                    y: centerY
                };
            default:
                return { x: centerX, y: centerY };
        }
    }

    handleComponentDragStart(e, component) {
        e.dataTransfer.setData('text/plain', 'move');
        e.dataTransfer.effectAllowed = 'move';
        
        // Store the component being dragged
        this.draggedComponent = component;
    }

    initComponentPropertiesPanel() {
        // Create properties panel if it doesn't exist
        if (!document.getElementById('properties-panel')) {
            const propertiesPanel = document.createElement('div');
            propertiesPanel.id = 'properties-panel';
            propertiesPanel.className = 'properties-panel';
            propertiesPanel.innerHTML = `
                <div class="panel-header">
                    <h4>Component Properties</h4>
                    <button class="btn-close" id="close-properties">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="panel-content">
                    <div class="property-group">
                        <label>Component Name</label>
                        <input type="text" id="prop-name" class="property-input">
                    </div>
                    <div class="property-group">
                        <label>Component Type</label>
                        <select id="prop-type" class="property-input" disabled>
                            <option value="user">User</option>
                            <option value="external_system">External System</option>
                            <option value="web_server">Web Server</option>
                            <option value="api_endpoint">API Endpoint</option>
                            <option value="database">Database</option>
                            <option value="file_system">File System</option>
                        </select>
                    </div>
                    <div class="property-group">
                        <label>Data Sensitivity</label>
                        <select id="prop-sensitivity" class="property-input">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="property-group">
                        <label>Description</label>
                        <textarea id="prop-description" class="property-input" rows="3" placeholder="Optional description..."></textarea>
                    </div>
                    <div class="property-actions">
                        <button class="btn-threat btn-primary" id="save-properties">Save</button>
                        <button class="btn-threat btn-secondary" id="delete-component">Delete</button>
                    </div>
                </div>
            `;
            
            document.querySelector('.modeling-container').appendChild(propertiesPanel);
            
            // Event listeners for properties panel
            document.getElementById('save-properties').addEventListener('click', () => this.saveComponentProperties());
            document.getElementById('delete-component').addEventListener('click', () => this.deleteSelectedComponent());
            document.getElementById('close-properties').addEventListener('click', () => this.hidePropertiesPanel());
        }
    }

    showComponentProperties(componentEl) {
        const component = this.findComponentById(componentEl.id);
        if (!component) return;
        
        // Populate properties panel
        document.getElementById('prop-name').value = component.name;
        document.getElementById('prop-type').value = component.type;
        document.getElementById('prop-sensitivity').value = component.sensitivity;
        document.getElementById('prop-description').value = component.description || '';
        
        // Store reference to current component
        this.selectedComponent = component;
        
        // Show properties panel
        document.getElementById('properties-panel').classList.add('visible');
    }

    hidePropertiesPanel() {
        document.getElementById('properties-panel').classList.remove('visible');
        this.selectedComponent = null;
    }

    saveComponentProperties() {
        if (!this.selectedComponent) return;
        
        const component = this.selectedComponent;
        component.name = document.getElementById('prop-name').value;
        component.sensitivity = document.getElementById('prop-sensitivity').value;
        component.description = document.getElementById('prop-description').value;
        
        // Update visual representation
        const componentEl = document.getElementById(component.id);
        if (componentEl) {
            componentEl.querySelector('.component-name').textContent = component.name;
            componentEl.querySelector('.component-sensitivity').textContent = component.sensitivity.toUpperCase();
            componentEl.querySelector('.component-sensitivity').className = `component-sensitivity sensitivity-${component.sensitivity}`;
        }
        
        this.showNotification('Component properties saved', 'success');
        this.hidePropertiesPanel();
    }

    deleteSelectedComponent() {
        if (!this.selectedComponent) return;
        
        if (confirm(`Delete component "${this.selectedComponent.name}"?`)) {
            const componentId = this.selectedComponent.id;
            
            // Remove from system data
            this.systemData.components = this.systemData.components.filter(c => c.id !== componentId);
            
            // Remove connected data flows
            this.systemData.data_flows = this.systemData.data_flows.filter(
                flow => flow.sourceId !== componentId && flow.destinationId !== componentId
            );
            
            // Remove from canvas
            const componentEl = document.getElementById(componentId);
            if (componentEl) {
                componentEl.remove();
            }
            
            // Remove connections visually
            document.querySelectorAll('.data-flow-connection').forEach(conn => {
                if (conn.id.startsWith('conn-')) {
                    const connId = conn.id;
                    if (!this.systemData.data_flows.some(f => f.id === connId)) {
                        conn.remove();
                    }
                }
            });
            
            this.showNotification('Component deleted', 'success');
            this.hidePropertiesPanel();
        }
    }

    findComponentById(id) {
        return this.systemData.components.find(c => c.id === id);
    }

    toggleConnectionMode() {
        if (this.diagramState === 'connecting') {
            this.cancelConnection();
            this.showNotification('Connection mode disabled', 'info');
        } else {
            this.diagramState = 'connecting';
            document.getElementById('flow-canvas').classList.add('connection-mode');
            this.showNotification('Click on source component to start connection', 'info');
        }
    }

    clearCanvas() {
        if (confirm('Clear the entire diagram? This will remove all components and connections.')) {
            this.systemData.components = [];
            this.systemData.data_flows = [];
            document.getElementById('flow-canvas').innerHTML = `
                <div class="canvas-placeholder">
                    <i class="fas fa-arrow-right"></i>
                    <p>Drag components here to build your data flow diagram</p>
                </div>
            `;
            this.hidePropertiesPanel();
            this.showNotification('Diagram cleared', 'info');
        }
    }

    autoLayout() {
        if (this.systemData.components.length === 0) {
            this.showNotification('No components to arrange', 'warning');
            return;
        }

        const canvas = document.getElementById('flow-canvas');
        const canvasRect = canvas.getBoundingClientRect();
        const centerX = canvasRect.width / 2;
        const centerY = canvasRect.height / 2;
        
        // Simple circular layout
        const radius = Math.min(canvasRect.width, canvasRect.height) * 0.3;
        const angleStep = (2 * Math.PI) / this.systemData.components.length;
        
        this.systemData.components.forEach((component, index) => {
            const angle = index * angleStep;
            const x = centerX + radius * Math.cos(angle) - 60;
            const y = centerY + radius * Math.sin(angle) - 40;
            
            component.position = { x, y };
            
            const componentEl = document.getElementById(component.id);
            if (componentEl) {
                componentEl.style.left = x + 'px';
                componentEl.style.top = y + 'px';
            }
        });
        
        // Update all connections
        this.systemData.data_flows.forEach(connection => {
            this.renderConnection(connection);
        });
        
        this.showNotification('Components auto-arranged', 'success');
    }

    filterComponents(searchTerm) {
        const components = document.querySelectorAll('.component-item');
        const categories = document.querySelectorAll('.component-category');
        
        let hasVisibleComponents = false;
        
        categories.forEach(category => {
            const categoryComponents = category.querySelectorAll('.component-item');
            let categoryHasVisible = false;
            
            categoryComponents.forEach(comp => {
                const text = comp.textContent.toLowerCase();
                if (text.includes(searchTerm.toLowerCase())) {
                    comp.style.display = 'flex';
                    categoryHasVisible = true;
                    hasVisibleComponents = true;
                } else {
                    comp.style.display = 'none';
                }
            });
            
            // Show/hide category based on whether it has visible components
            category.style.display = categoryHasVisible ? 'block' : 'none';
        });
    }

    async analyzeThreats() {
        if (!this.systemData.name) {
            this.showNotification('Please enter a system name', 'error');
            return;
        }

        if (this.systemData.components.length === 0) {
            this.showNotification('Please add at least one system component', 'error');
            return;
        }

        if (this.systemData.methodologies.length === 0) {
            this.showNotification('Please select at least one analysis methodology', 'error');
            return;
        }

        this.showLoading('Analyzing threats with selected methodologies...');

        try {
            const requestData = {
                'tool': 'threat_modeling',
                'system_data': JSON.stringify(this.systemData),
                'analysis_type': this.systemData.analysis_scope || 'comprehensive',
                'methodologies': this.systemData.methodologies.join(','),
                'frameworks': this.systemData.frameworks.join(',')
            };

            console.log('Sending analysis request with:', requestData);

            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(requestData)
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            console.log('Raw response:', responseText);

            if (!responseText) {
                throw new Error('Empty response from server');
            }

            let results;
            try {
                results = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text that failed to parse:', responseText);
                throw new Error('Invalid JSON response from server');
            }

            // Enhanced validation for results
            if (results && results.success && results.data) {
                this.displayEnhancedResults(results.data);
                this.showNotification('Enhanced threat analysis completed!', 'success');
            } else {
                throw new Error(results?.error || 'Threat analysis failed - no data returned');
            }

        } catch (error) {
            console.error('Threat analysis error:', error);
            this.showError('Failed to analyze threats: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }



    showLoading() {
        document.getElementById('threat-loading').style.display = 'flex';
        document.querySelector('.modeling-container').style.opacity = '0.5';
        document.querySelector('.modeling-container').style.pointerEvents = 'none';
    }

    hideLoading() {
        document.getElementById('threat-loading').style.display = 'none';
        document.querySelector('.modeling-container').style.opacity = '1';
        document.querySelector('.modeling-container').style.pointerEvents = 'auto';
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    getNotificationIcon(type) {
        const icons = {
            'info': 'info-circle',
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'error': 'times-circle'
        };
        return icons[type] || 'info-circle';
    }

    showError(message) {
        console.error('Threat Modeling Error:', message);
        this.showNotification(message, 'error');
        
        // Show error in results section if available
        const resultsSection = document.getElementById('threat-results');
        if (resultsSection && resultsSection.style.display !== 'none') {
            resultsSection.innerHTML = `
                <div class="error-results">
                    <div class="error-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Analysis Error</h3>
                    </div>
                    <div class="error-content">
                        <p>${message}</p>
                        <button class="btn-threat btn-primary" onclick="window.threatModeler.returnToModeling()">
                            <i class="fas fa-arrow-left"></i> Return to Modeling
                        </button>
                    </div>
                </div>
            `;
        }
    }

    returnToModeling() {
        document.querySelector('.modeling-container').style.display = 'block';
        document.getElementById('threat-results').style.display = 'none';
    }

    displayEnhancedResults(results) {
    // Enhanced null/undefined checking
        if (!results) {
            console.error('Results is null or undefined');
            this.showError('No analysis results received from server');
            return;
        }

        console.log('Displaying results:', results);
        
        // Store current results for reference
        window.currentResults = results;
        
        document.querySelector('.modeling-container').style.display = 'none';
        document.getElementById('threat-results').style.display = 'block';

        // Safe display methods with fallbacks
        this.displayExecutiveSummary(results.executive_summary);
        this.displayEnhancedThreatAnalysis(results.threat_analysis);
        
        // Use enhanced risk assessment that counts all threats
        const enhancedAssessment = this.calculateEnhancedRiskAssessment(
            results.threat_analysis, 
            this.systemData
        );
        this.displayEnhancedRiskAssessment(enhancedAssessment);
        
        // Use attack_paths from root level, fallback to threat_analysis
        const attackPaths = results.attack_paths || (results.threat_analysis ? results.threat_analysis.attack_paths : []);
        this.displayAttackPaths(attackPaths);
        
        // Enhanced AI threats display
        const aiThreats = results.threat_analysis?.ai_discovered || 
                        results.ai_discovered_threats || 
                        results.threat_analysis?.ai_threats;
        this.displayAIDiscoveredThreats(aiThreats);
        
        this.displayMitigationStrategies(results.mitigation_strategies);
        this.displayEnhancedRecommendations(results.recommendations);
    }

    displayDataNotAvailable(section, message) {
        return `
            <div class="data-not-available">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>${section} Data Unavailable</h4>
                <p>${message}</p>
            </div>
        `;
    }

    displayExecutiveSummary(summary) {
        // Safe summary handling
        if (!summary) {
            summary = {
                system_name: 'Unknown System',
                analysis_date: new Date().toLocaleDateString(),
                overall_risk_level: 'unknown',
                total_threats_identified: 0,
                critical_threats: 0,
                key_findings: ['No executive summary available'],
                next_steps: ['Review the detailed analysis below']
            };
        }

        const html = `
            <div class="executive-summary">
                <div class="summary-header">
                    <h3>${summary.system_name || 'Unknown System'} - Threat Analysis</h3>
                    <div class="summary-meta">
                        <span><i class="fas fa-calendar"></i> ${summary.analysis_date || new Date().toLocaleDateString()}</span>
                        <span class="risk-badge risk-${summary.overall_risk_level || 'unknown'}">
                            ${(summary.overall_risk_level || 'unknown').toUpperCase()} RISK
                        </span>
                    </div>
                </div>
                <div class="summary-metrics">
                    <div class="summary-metric">
                        <span class="metric-value">${summary.total_threats_identified || 0}</span>
                        <span class="metric-label">Total Threats</span>
                    </div>
                    <div class="summary-metric">
                        <span class="metric-value">${summary.critical_threats || 0}</span>
                        <span class="metric-label">Critical Threats</span>
                    </div>
                </div>
                <div class="key-findings">
                    <h4>Key Findings</h4>
                    <ul>
                        ${(summary.key_findings || ['No key findings available']).map(finding => `<li>${finding}</li>`).join('')}
                    </ul>
                </div>
                <div class="next-steps">
                    <h4>Recommended Next Steps</h4>
                    <div class="steps">
                        ${(summary.next_steps || ['Review detailed threat analysis']).map(step => `
                            <div class="step-item">
                                <i class="fas fa-arrow-right"></i>
                                <span>${step}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;

        const section = document.querySelector('#threat-results .result-section:first-child');
        if (section) {
            section.innerHTML = html;
        }
    }

    displayEnhancedThreatAnalysis(threats) {
        console.log("Raw threats data from PHP:", threats);
        
        // Handle null/undefined threats
        if (!threats) {
            threats = {};
        }
        
        let html = '<h3><i class="fas fa-bug"></i> Threat Analysis</h3>';
        
        // STRIDE Analysis with enhanced checking
        if (threats.stride) {
            console.log("STRIDE data:", threats.stride);
            if (threats.stride._note) {
                html += this.displayDataNotAvailable('STRIDE Analysis', threats.stride._note);
            } else {
                let hasThreats = false;
                for (const [category, threatList] of Object.entries(threats.stride)) {
                    if (category !== '_note' && Array.isArray(threatList) && threatList.length > 0) {
                        hasThreats = true;
                        break;
                    }
                }
                
                if (hasThreats) {
                    html += '<div class="analysis-section"><h4>STRIDE Analysis</h4>';
                    for (const [category, threatList] of Object.entries(threats.stride)) {
                        if (category !== '_note' && Array.isArray(threatList) && threatList.length > 0) {
                            html += `
                                <div class="threat-category">
                                    <h5 class="category-${category}">${this.formatCategoryName(category)}</h5>
                                    <div class="threat-list">
                                        ${threatList.map(threat => this.createThreatHTML(threat)).join('')}
                                    </div>
                                </div>
                            `;
                        }
                    }
                    html += '</div>';
                } else {
                    html += this.displayDataNotAvailable('STRIDE Analysis', 'No STRIDE threats identified for this system configuration.');
                }
            }
        }

        // DREAD Analysis
        if (threats.dread) {
            if (threats.dread._note) {
                html += this.displayDataNotAvailable('DREAD Analysis', threats.dread._note);
            } else if (Array.isArray(threats.dread) && threats.dread.length > 0) {
                html += `
                    <div class="analysis-section">
                        <h4>DREAD Scoring</h4>
                        <div class="dread-grid">
                            ${threats.dread.map(threat => this.createDREADThreatHTML(threat)).join('')}
                        </div>
                    </div>
                `;
            } else {
                html += this.displayDataNotAvailable('DREAD Analysis', 'No DREAD threats identified for this system configuration.');
            }
        }

        // OWASP Analysis
        if (threats.owasp) {
            if (threats.owasp._note) {
                html += this.displayDataNotAvailable('OWASP Analysis', threats.owasp._note);
            } else if (Array.isArray(threats.owasp) && threats.owasp.length > 0) {
                html += this.displayOWASPThreats(threats.owasp);
            } else {
                html += this.displayDataNotAvailable('OWASP Analysis', 'No OWASP threats identified for this system configuration.');
            }
        }

        // MITRE ATT&CK
        if (threats.mitre) {
            if (threats.mitre._note) {
                html += this.displayDataNotAvailable('MITRE ATT&CK Analysis', threats.mitre._note);
            } else if (threats.mitre && Object.keys(threats.mitre).length > 0) {
                html += `
                    <div class="analysis-section">
                        <h4>MITRE ATT&CK</h4>
                        <div class="mitre-grid">
                            ${Object.values(threats.mitre).map(technique => this.createMITREThreatHTML(technique)).join('')}
                        </div>
                    </div>
                `;
            } else {
                html += this.displayDataNotAvailable('MITRE ATT&CK Analysis', 'No MITRE ATT&CK techniques identified for this system configuration.');
            }
        }

        // CWE Analysis
        if (threats.cwe) {
            console.log("CWE data:", threats.cwe);
            if (threats.cwe._note) {
                console.log("CWE has note:", threats.cwe._note);
                html += this.displayDataNotAvailable('CWE Analysis', threats.cwe._note);
            } else if (Array.isArray(threats.cwe) && threats.cwe.length > 0) {
                console.log("CWE has", threats.cwe.length, "entries");
                html += this.displayCWEThreats(threats.cwe);
            } else {
                console.log("CWE is empty array or invalid format");
                html += this.displayDataNotAvailable('CWE Analysis', 'No CWE weaknesses identified for this system configuration.');
            } 
        }

        // NIST Analysis
        if (threats.nist) {
            if (threats.nist._note) {
                html += this.displayDataNotAvailable('NIST CSF Analysis', threats.nist._note);
            } else if (Array.isArray(threats.nist) && threats.nist.length > 0) {
                html += this.displayNISTThreats(threats.nist);
            } else {
                html += this.displayDataNotAvailable('NIST CSF Analysis', 'No NIST CSF controls identified for this system configuration.');
            }
        }

        // CIS Analysis
        if (threats.cis) {
            if (threats.cis._note) {
                html += this.displayDataNotAvailable('CIS Controls Analysis', threats.cis._note);
            } else if (Array.isArray(threats.cis) && threats.cis.length > 0) {
                html += this.displayCISThreats(threats.cis);
            } else {
                html += this.displayDataNotAvailable('CIS Controls Analysis', 'No CIS Controls identified for this system configuration.');
            }
        }

        // ISO 27001 Analysis
        if (threats.iso27001) {
            if (threats.iso27001._note) {
                html += this.displayDataNotAvailable('ISO 27001 Analysis', threats.iso27001._note);
            } else if (Array.isArray(threats.iso27001) && threats.iso27001.length > 0) {
                html += this.displayISO27001Threats(threats.iso27001);
            } else {
                html += this.displayDataNotAvailable('ISO 27001 Analysis', 'No ISO 27001 controls identified for this system configuration.');
            }
        }

        // AI Discovered Threats - ENHANCED HANDLING
        const aiThreats = threats.ai_discovered || threats.ai_threats || threats.ai_discovered_threats;
        if (aiThreats) {
            if (aiThreats._note) {
                html += this.displayDataNotAvailable('AI Threat Analysis', aiThreats._note);
            } else if (Array.isArray(aiThreats) && aiThreats.length > 0) {
                html += this.displayAIDiscoveredThreats(aiThreats);
            } else {
                html += this.displayDataNotAvailable('AI Threat Analysis', 'No AI-discovered threats identified for this system configuration.');
            }
        }

        // Check if no analysis was performed at all
        const hasAnyAnalysis = threats.stride || threats.dread || threats.owasp || threats.mitre || threats.cwe || aiThreats;
        if (!hasAnyAnalysis) {
            html = '<h3><i class="fas fa-bug"></i> Enhanced Threat Analysis</h3>' +
                this.displayDataNotAvailable('Threat Analysis', 'No threat analysis data is currently available. Please check your internet connection and try again.');
        }

        const section = document.querySelector('#threat-results .result-section:nth-child(2)');
        if (section) {
            section.innerHTML = html;
            // Initialize enhanced read more functionality
            this.initEnhancedReadMore();
        }
    }

    displayDataNotAvailable(section, message) {
        return `
            <div class="analysis-section">
                <h4>${section}</h4>
                <div class="data-not-available">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h5>Data Currently Unavailable</h5>
                    <p>${message}</p>
                    <small><i class="fas fa-info-circle"></i> This section relies on external security databases that may be temporarily unavailable.</small>
                </div>
            </div>
        `;
    }

    displayCWEThreats(cweThreats) {
        if (!cweThreats || cweThreats.length === 0) return '';

        return `
            <div class="analysis-section">
                <h4>CWE Analysis</h4>
                <div class="cwe-grid">
                    ${cweThreats.map(weakness => {
                        const description = this.createTruncatedText(weakness.description, 600); // 600 chars
                        
                        return `
                            <div class="cwe-item cwe-${weakness.risk_level}">
                                <div class="cwe-header">
                                    <h5>${weakness.cwe_id}: ${weakness.name}</h5>
                                    <span class="cwe-risk risk-${weakness.risk_level}">
                                        ${weakness.risk_level.toUpperCase()}
                                    </span>
                                </div>
                                <div class="cwe-details">
                                    <div class="cwe-description">
                                        ${description}
                                    </div>
                                    <p><strong>Components:</strong> ${weakness.applicable_components.join(', ')}</p>
                                    <div class="cwe-mitigation">
                                        <strong>Mitigation:</strong>
                                        <ul>
                                            ${weakness.mitigation.map(m => `<li>${m}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    // In the displayAIDiscoveredThreats method, update the description handling:
    // displayAIDiscoveredThreats(aiThreats) {
    //     if (!aiThreats || !Array.isArray(aiThreats) || aiThreats.length === 0) return '';

    //     console.log("Displaying AI threats:", aiThreats);

    //     return `
    //         <div class="analysis-section">
    //             <h4><i class="fas fa-robot"></i> AI-Discovered Threats</h4>
    //             <div class="ai-analysis-info">
    //                 <div class="ai-info-banner">
    //                     <i class="fas fa-lightbulb"></i>
    //                     <span>AI analysis provides contextual threat intelligence based on your system architecture</span>
    //                 </div>
    //             </div>
    //             <div class="ai-threats-grid">
    //                 ${aiThreats.map((threat, index) => {
    //                     // Enhanced threat data handling
    //                     const threatName = threat.name || threat.threat_name || `AI Threat ${index + 1}`;
    //                     const description = this.formatAIDescription(threat.description || threat.threat_description || 'No description available');
    //                     const riskLevel = threat.risk_level || threat.risk || 'medium';
    //                     const category = threat.category || threat.threat_category || 'AI Analysis';
    //                     const confidence = threat.confidence || 0.7;
    //                     const components = threat.components || threat.applicable_components || ['System-wide'];
    //                     const mitigation = threat.mitigation || threat.mitigations || ['Review and implement appropriate security controls'];
                        
    //                     // Use a much higher character limit for AI descriptions
    //                     const formattedDescription = this.createTruncatedText(description, 2000);
                        
    //                     return `
    //                         <div class="ai-threat-item threat-${riskLevel}">
    //                             <div class="ai-threat-header">
    //                                 <div class="ai-threat-title">
    //                                     <h5>${threatName}</h5>
    //                                     <div class="ai-threat-meta">
    //                                         <span class="confidence">
    //                                             <i class="fas fa-brain"></i> 
    //                                             Confidence: ${(confidence * 100).toFixed(0)}%
    //                                         </span>
    //                                         <span class="threat-risk risk-${riskLevel}">
    //                                             ${riskLevel.toUpperCase()}
    //                                         </span>
    //                                     </div>
    //                                 </div>
    //                                 <div class="ai-threat-category">
    //                                     <i class="fas fa-tag"></i> ${category}
    //                                 </div>
    //                             </div>
    //                             <div class="ai-threat-content">
    //                                 <div class="ai-threat-description">
    //                                     ${formattedDescription}
    //                                 </div>
    //                                 <div class="ai-threat-details">
    //                                     <div class="ai-threat-components">
    //                                         <strong><i class="fas fa-microchip"></i> Affected Components:</strong>
    //                                         <span>${Array.isArray(components) ? components.join(', ') : components}</span>
    //                                     </div>
    //                                     <div class="ai-threat-mitigation">
    //                                         <strong><i class="fas fa-shield-alt"></i> Recommended Mitigations:</strong>
    //                                         <ul>
    //                                             ${(Array.isArray(mitigation) ? mitigation : [mitigation])
    //                                                 .map(m => `<li>${m}</li>`)
    //                                                 .join('')}
    //                                         </ul>
    //                                     </div>
    //                                 </div>
    //                             </div>
    //                             <div class="ai-threat-footer">
    //                                 <span class="ai-threat-source">
    //                                     <i class="fas fa-robot"></i> AI Threat Intelligence
    //                                 </span>
    //                                 ${threat.domain_specific ? 
    //                                     `<span class="domain-badge">
    //                                         <i class="fas fa-globe"></i> ${threat.domain_specific}
    //                                     </span>` : ''
    //                                 }
    //                             </div>
    //                         </div>
    //                     `;
    //                 }).join('')}
    //             </div>
    //         </div>
    //     `;
    // }
    displayAIDiscoveredThreats(aiThreats) {
        if (!aiThreats || !Array.isArray(aiThreats) || aiThreats.length === 0) {
            return '';
        }

        console.log("Displaying AI threats:", aiThreats);

        return `
            <div class="analysis-section">
                <h4><i class="fas fa-robot"></i> AI Threat Intelligence</h4>
                <div class="ai-analysis-info">
                    <div class="ai-info-banner">
                        <i class="fas fa-lightbulb"></i>
                        <span>AI analysis provides contextual threat intelligence based on your system architecture</span>
                    </div>
                </div>
                <div class="ai-threats-grid">
                    ${aiThreats.map((threat, index) => {
                        // Enhanced threat data handling with better fallbacks
                        const threatName = threat.name || threat.threat_name || `AI Threat ${index + 1}`;
                        const description = this.formatAIDescription(threat.description || threat.threat_description || 'No description available');
                        const riskLevel = threat.risk_level || threat.risk || 'medium';
                        const category = threat.category || threat.threat_category || 'AI Analysis';
                        const confidence = threat.confidence || 0.7;
                        const components = threat.components || threat.applicable_components || ['System-wide'];
                        const mitigation = threat.mitigation || threat.mitigations || ['Review and implement appropriate security controls'];
                        const domainSpecific = threat.domain_specific || threat.domain || '';
                        
                        // Create comprehensive threat content
                        return this.createAIThreatHTML({
                            id: `ai-threat-${index}`,
                            name: threatName,
                            description: description,
                            riskLevel: riskLevel,
                            category: category,
                            confidence: confidence,
                            components: components,
                            mitigation: mitigation,
                            domainSpecific: domainSpecific
                        });
                    }).join('')}
                </div>
            </div>
        `;
    }

    // New method to create well-structured AI threat HTML
createAIThreatHTML(threatData) {
    const {
        id,
        name,
        description,
        riskLevel,
        category,
        confidence,
        components,
        mitigation,
        domainSpecific
    } = threatData;

    // Format confidence as percentage
        const confidencePercent = Math.round(confidence * 100);
        const confidenceClass = confidencePercent >= 80 ? 'high' : confidencePercent >= 60 ? 'medium' : 'low';

        // Process components array
        const componentsList = Array.isArray(components) ? components : [components];
        const componentsText = componentsList.join(', ');

        // Process mitigation array
        const mitigationList = Array.isArray(mitigation) ? mitigation : [mitigation];

        return `
            <div class="ai-threat-item threat-${riskLevel}" id="${id}">
                <div class="ai-threat-header">
                    <div class="ai-threat-title">
                        <h5>${this.escapeHtml(name)}</h5>
                        <div class="ai-threat-meta">
                            <span class="confidence confidence-${confidenceClass}">
                                <i class="fas fa-brain"></i> 
                                Confidence: ${confidencePercent}%
                            </span>
                            <span class="threat-risk risk-${riskLevel}">
                                ${riskLevel.toUpperCase()}
                            </span>
                        </div>
                    </div>
                    <div class="ai-threat-category">
                        <i class="fas fa-tag"></i> ${this.escapeHtml(category)}
                    </div>
                </div>
                
                <div class="ai-threat-content">
                    <div class="ai-threat-description">
                        <div class="description-content">
                            ${this.createEnhancedDescription(description)}
                        </div>
                    </div>
                    
                    <div class="ai-threat-details-grid">
                        <div class="detail-group">
                            <div class="detail-label">
                                <i class="fas fa-microchip"></i>
                                <strong>Affected Components</strong>
                            </div>
                            <div class="detail-value">
                                ${this.escapeHtml(componentsText)}
                            </div>
                        </div>
                        
                        <div class="detail-group">
                            <div class="detail-label">
                                <i class="fas fa-shield-alt"></i>
                                <strong>Recommended Mitigations</strong>
                            </div>
                            <div class="detail-value">
                                <ul class="mitigation-list">
                                    ${mitigationList.map(m => `
                                        <li>${this.escapeHtml(m)}</li>
                                    `).join('')}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="ai-threat-footer">
                    <span class="ai-threat-source">
                        <i class="fas fa-robot"></i> AI Threat Intelligence
                    </span>
                    ${domainSpecific ? `
                        <span class="domain-badge">
                            <i class="fas fa-globe"></i> ${this.escapeHtml(domainSpecific)}
                        </span>
                    ` : ''}
                </div>
            </div>
        `;
    }

    // Enhanced description formatting
createEnhancedDescription(description) {
    if (!description) {
        return '<div class="no-description">No description available</div>';
        }

        const cleanDescription = this.formatAIDescription(description);
        
        // For AI content, be more generous with length limits
        const maxLength = 1500; // Increased character limit for AI content
        
        if (cleanDescription.length <= maxLength) {
            return `<div class="full-description">${cleanDescription}</div>`;
        }

        const truncated = cleanDescription.substring(0, maxLength) + '...';
        const randomId = 'desc-' + Math.random().toString(36).substr(2, 9);
        
        return `
            <div class="truncated-description">
                <div class="description-preview" id="${randomId}">
                    ${truncated}
                </div>
                <button class="read-more-btn" data-target="${randomId}" data-full="${this.escapeHtml(cleanDescription)}">
                    <span class="btn-text">Read more</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        `;
    }


    displayNISTThreats(nistThreats) {
        if (!nistThreats || nistThreats.length === 0) return '';

        return `
            <div class="analysis-section">
                <h4>NIST CSF Controls</h4>
                <div class="nist-grid">
                    ${nistThreats.map(control => {
                        const description = this.createTruncatedText(control.description, 600);
                        
                        return `
                            <div class="nist-item nist-${control.risk_level}">
                                <div class="nist-header">
                                    <h5>${control.control_id}: ${control.name}</h5>
                                    <div class="nist-meta">
                                        <span class="nist-function">${control.function}</span>
                                        <span class="nist-risk risk-${control.risk_level}">
                                            ${control.risk_level.toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                                <div class="nist-details">
                                    <div class="nist-description">
                                        ${description}
                                    </div>
                                    <p><strong>Category:</strong> ${control.category}</p>
                                    <p><strong>Components:</strong> ${control.applicable_components.join(', ')}</p>
                                    <p><strong>Maturity Level:</strong> ${control.maturity_level}</p>
                                    <div class="nist-implementation">
                                        <strong>Implementation Guidance:</strong>
                                        <ul>
                                            ${control.implementation_guidance.map(g => `<li>${g}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    displayCISThreats(cisThreats) {
        if (!cisThreats || cisThreats.length === 0) return '';

        return `
            <div class="analysis-section">
                <h4>CIS Critical Security Controls</h4>
                <div class="cis-grid">
                    ${cisThreats.map(control => {
                        const description = this.createTruncatedText(control.description, 600);
                        
                        return `
                            <div class="cis-item cis-${control.risk_level}">
                                <div class="cis-header">
                                    <h5>${control.control_id}: ${control.name}</h5>
                                    <div class="cis-meta">
                                        <span class="cis-safeguard">${control.safeguard}</span>
                                        <span class="cis-risk risk-${control.risk_level}">
                                            ${control.risk_level.toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                                <div class="cis-details">
                                    <div class="cis-description">
                                        ${description}
                                    </div>
                                    <p><strong>Components:</strong> ${control.applicable_components.join(', ')}</p>
                                    <p><strong>Assurance Level:</strong> ${control.assurance_level}</p>
                                    <div class="cis-implementation">
                                        <strong>Implementation:</strong>
                                        <ul>
                                            ${control.implementation.map(i => `<li>${i}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    displayISO27001Threats(isoThreats) {
        if (!isoThreats || isoThreats.length === 0) return '';

        return `
            <div class="analysis-section">
                <h4>ISO 27001 Controls</h4>
                <div class="iso-grid">
                    ${isoThreats.map(control => {
                        const description = this.createTruncatedText(control.description, 600);
                        
                        return `
                            <div class="iso-item iso-${control.risk_level}">
                                <div class="iso-header">
                                    <h5>${control.control_id}: ${control.name}</h5>
                                    <div class="iso-meta">
                                        <span class="iso-annex">${control.annex}</span>
                                        <span class="iso-risk risk-${control.risk_level}">
                                            ${control.risk_level.toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                                <div class="iso-details">
                                    <div class="iso-description">
                                        ${description}
                                    </div>
                                    <p><strong>Domain:</strong> ${control.domain}</p>
                                    <p><strong>Components:</strong> ${control.applicable_components.join(', ')}</p>
                                    <p><strong>Compliance Level:</strong> ${control.compliance_level}</p>
                                    <div class="iso-implementation">
                                        <strong>Implementation:</strong>
                                        <ul>
                                            ${control.implementation.map(i => `<li>${i}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    // Helper method to format AI description
    // formatAIDescription(description) {
    //     if (!description) return 'No description available';
        
    //     if (Array.isArray(description)) {
    //         return description.join(' ');
    //     }
        
    //     if (typeof description === 'string') {
    //         // Enhanced cleaning for AI responses
    //         let cleaned = description
    //             .replace(/\[.*?\]/g, '') // Remove bracketed content
    //             .replace(/\*\*(.*?)\*\*/g, '$1') // Remove markdown bold but keep text
    //             .replace(/\*(.*?)\*/g, '$1') // Remove markdown italic but keep text
    //             .replace(/`(.*?)`/g, '$1') // Remove code formatting
    //             .replace(/#{1,6}\s?/g, '') // Remove markdown headers
    //             .replace(/\n{3,}/g, '\n\n') // Normalize multiple newlines
    //             .replace(/\s+/g, ' ') // Normalize whitespace
    //             .trim();
            
    //         // Fix common AI response patterns that get truncated
    //         cleaned = this.fixAITruncationPatterns(cleaned);
            
    //         // Ensure proper sentence structure
    //         cleaned = this.ensureCompleteSentences(cleaned);
            
    //         return cleaned;
    //     }
        
    //     return String(description);
    // }

    formatAIDescription(description) {
        if (!description) return 'No description available';
        
        if (Array.isArray(description)) {
            // Join array elements with proper spacing
            return description.map(item => item.trim()).filter(item => item.length > 0).join(' ');
        }
        
        if (typeof description === 'string') {
            // Enhanced cleaning for AI responses
            let cleaned = description
                .replace(/\[.*?\]/g, '') // Remove bracketed content like [image]
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Convert markdown bold to HTML
                .replace(/\*(.*?)\*/g, '<em>$1</em>') // Convert markdown italic to HTML
                .replace(/`(.*?)`/g, '<code>$1</code>') // Convert code formatting
                .replace(/#{1,6}\s?/g, '') // Remove markdown headers
                .replace(/\n{3,}/g, '\n\n') // Normalize multiple newlines
                .replace(/\s+/g, ' ') // Normalize whitespace
                .trim();
            
            // Fix common AI response patterns
            cleaned = this.fixAITruncationPatterns(cleaned);
            
            // Ensure proper sentence structure
            cleaned = this.ensureCompleteSentences(cleaned);
            
            // Convert line breaks to HTML
            cleaned = cleaned.replace(/\n/g, '<br>');
            
            return cleaned;
        }
        
        return String(description);
    }

    // fixAITruncationPatterns(text) {
    //     if (!text) return text;
        
    //     // Fix incomplete sentences at the end
    //     const sentences = text.split(/[.!?]+/).filter(s => s.trim().length > 0);
        
    //     if (sentences.length === 0) return text;
        
    //     // If the last sentence doesn't end with proper punctuation and is too short, 
    //     // it might be truncated - remove it
    //     const lastSentence = sentences[sentences.length - 1].trim();
    //     if (lastSentence.length < 20 && !text.endsWith('.') && !text.endsWith('!') && !text.endsWith('?')) {
    //         sentences.pop();
    //         return sentences.join('. ') + '.';
    //     }
        
    //     return text;
    // }

    fixAITruncationPatterns(text) {
        if (!text) return text;
        
        // Remove incomplete sentences at the end
        const sentences = text.split(/[.!?]+/).filter(s => s.trim().length > 0);
        
        if (sentences.length === 0) return text;
        
        // If the last sentence is too short and doesn't end with proper punctuation, remove it
        const lastSentence = sentences[sentences.length - 1].trim();
        if (lastSentence.length < 25 && !text.endsWith('.') && !text.endsWith('!') && !text.endsWith('?')) {
            sentences.pop();
            return sentences.join('. ') + '.';
        }
        
        return text;
    }

    // New method to ensure complete sentences
    ensureCompleteSentences(text) {
        if (!text) return text;
        
        // Add period if text doesn't end with proper punctuation
        if (!/[.!?]$/.test(text)) {
            return text + '.';
        }
        
        return text;
    }

    // Enhanced read more functionality for AI descriptions
    initEnhancedReadMore() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.read-more-btn')) {
                const button = e.target.closest('.read-more-btn');
                const targetId = button.getAttribute('data-target');
                const fullText = button.getAttribute('data-full');
                const textContainer = document.getElementById(targetId);
                
                if (textContainer && fullText) {
                    const isExpanded = textContainer.classList.contains('expanded');
                    
                    if (isExpanded) {
                        // Collapse
                        const truncated = fullText.substring(0, 1500) + '...';
                        textContainer.innerHTML = truncated;
                        textContainer.classList.remove('expanded');
                        button.classList.remove('expanded');
                        button.querySelector('.btn-text').textContent = 'Read more';
                        button.querySelector('i').className = 'fas fa-chevron-down';
                    } else {
                        // Expand - show full text
                        textContainer.innerHTML = fullText;
                        textContainer.classList.add('expanded');
                        button.classList.add('expanded');
                        button.querySelector('.btn-text').textContent = 'Read less';
                        button.querySelector('i').className = 'fas fa-chevron-up';
                    }
                }
            }
        });
    }

    

    createThreatHTML(threat) {
        const impact = this.createTruncatedText(threat.impact, 600); // 600 chars for impact
        
        return `
            <div class="threat-item threat-${threat.risk_level}">
                <div class="threat-header">
                    <h5>${threat.description}</h5>
                    <span class="threat-risk risk-${threat.risk_level}">
                        ${threat.risk_level.toUpperCase()}
                    </span>
                </div>
                <div class="threat-details">
                    <p><strong>Component:</strong> ${threat.component || threat.flow}</p>
                    <div class="threat-impact">
                        <strong>Impact:</strong> ${impact}
                    </div>
                    <p><strong>Likelihood:</strong> ${threat.likelihood}</p>
                </div>
            </div>
        `;
    }

    formatCategoryName(category) {
        const names = {
            'spoofing': 'Spoofing',
            'tampering': 'Tampering', 
            'repudiation': 'Repudiation',
            'information_disclosure': 'Information Disclosure',
            'denial_of_service': 'Denial of Service',
            'elevation_of_privilege': 'Elevation of Privilege'
        };
        return names[category] || category;
    }

    createDREADThreatHTML(threat) {
        return `
            <div class="dread-item dread-${threat.risk_level}">
                <div class="dread-header">
                    <h5>${threat.threat_type}</h5>
                    <span class="dread-score">DREAD: ${threat.dread_score}</span>
                </div>
                <div class="dread-details">
                    <p><strong>Component:</strong> ${threat.component}</p>
                    <div class="dread-breakdown">
                        <span>D:${threat.damage}</span>
                        <span>R:${threat.reproducibility}</span>
                        <span>E:${threat.exploitability}</span>
                        <span>A:${threat.affected_users}</span>
                        <span>D:${threat.discoverability}</span>
                    </div>
                </div>
            </div>
        `;
    }

    createMITREThreatHTML(technique) {
        const description = this.createTruncatedText(technique.description, 600); // 600 chars
        
        return `
            <div class="mitre-item mitre-${technique.risk_level}">
                <div class="mitre-header">
                    <h5>${technique.technique_id}: ${technique.name}</h5>
                    <span class="mitre-tactic">${technique.tactic}</span>
                </div>
                <div class="mitre-details">
                    <p><strong>Components:</strong> ${technique.applicable_components.join(', ')}</p>
                    <div class="mitre-description">
                        ${description}
                    </div>
                    <div class="mitre-mitigation">
                        <strong>Mitigation:</strong>
                        <ul>
                            ${technique.mitigation.map(m => `<li>${m}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            </div>
        `;
    }

    displayEnhancedRiskAssessment(assessment) {
        // Safe assessment handling
        if (!assessment) {
            assessment = {
                overall_risk_score: 0,
                attack_path_risk: 0,
                complexity_factor: 0,
                total_threats: 0,
                threat_counts: {
                    critical: 0,
                    high: 0,
                    medium: 0,
                    low: 0
                }
            };
        }

        const html = `
            <h3><i class="fas fa-chart-pie"></i> Risk Assessment</h3>
            <div class="risk-metrics-enhanced">
                <div class="risk-metric-card">
                    <div class="metric-value">${assessment.overall_risk_score || 0}%</div>
                    <div class="metric-label">Overall Risk Score</div>
                </div>
                <div class="risk-metric-card">
                    <div class="metric-value">${assessment.attack_path_risk || 0}%</div>
                    <div class="metric-label">Attack Path Risk</div>
                </div>
                <div class="risk-metric-card">
                    <div class="metric-value">${Math.round((assessment.complexity_factor || 0) * 100)}%</div>
                    <div class="metric-label">System Complexity</div>
                </div>
                <div class="risk-metric-card">
                    <div class="metric-value">${assessment.total_threats || 0}</div>
                    <div class="metric-label">Total Threats</div>
                </div>
            </div>
            <div class="threat-breakdown">
                <h4>Threat Breakdown</h4>
                <div class="breakdown-grid">
                    <div class="breakdown-item critical">
                        <span class="count">${assessment.threat_counts?.critical || 0}</span>
                        <span class="label">Critical</span>
                    </div>
                    <div class="breakdown-item high">
                        <span class="count">${assessment.threat_counts?.high || 0}</span>
                        <span class="label">High</span>
                    </div>
                    <div class="breakdown-item medium">
                        <span class="count">${assessment.threat_counts?.medium || 0}</span>
                        <span class="label">Medium</span>
                    </div>
                    <div class="breakdown-item low">
                        <span class="count">${assessment.threat_counts?.low || 0}</span>
                        <span class="label">Low</span>
                    </div>
                </div>
            </div>
        `;

        const section = document.querySelector('#threat-results .result-section:nth-child(3)');
        if (section) {
            section.innerHTML = html;
        }
    }

    displayAttackPaths(attackPaths) {
        // Enhanced attack paths handling
        if (!attackPaths || !Array.isArray(attackPaths)) {
            attackPaths = [];
        }

        if (attackPaths.length === 0) {
            // Check if we should show a placeholder
            const section = document.querySelector('#threat-results .result-section:nth-child(4)');
            if (section) {
                section.innerHTML = `
                    <h3><i class="fas fa-route"></i> Attack Path Analysis</h3>
                    <div class="data-not-available">
                        <i class="fas fa-info-circle"></i>
                        <h4>No Attack Paths Identified</h4>
                        <p>No specific attack paths were identified in this analysis. This could be due to simple system architecture or limited data flows.</p>
                    </div>
                `;
            }
            return;
        }

        let html = '<h3><i class="fas fa-route"></i> Attack Path Analysis</h3>';
        html += '<div class="attack-paths">';
        
        attackPaths.forEach(path => {
            const riskLevel = this.getPathRiskLevel(path.risk_score || 0);
            const threats = path.threats || [];
            
            html += `
                <div class="attack-path path-${riskLevel}">
                    <div class="path-header">
                        <h4>${path.source || 'Unknown'} → ${path.destination || 'Unknown'}</h4>
                        <span class="path-risk">Risk: ${path.risk_score || 0}%</span>
                    </div>
                    <div class="path-description">
                        <p>${path.description || 'No description available'}</p>
                    </div>
                    <div class="path-threats">
                        <strong>Key Threats (${threats.length}):</strong>
                        <div class="path-threats-list">
                            ${threats.map(threat => `
                                <div class="path-threat threat-${threat.risk_level || 'medium'}">
                                    <span class="threat-id">${threat.threat_id || 'Unknown'}</span>
                                    <span class="threat-desc">${threat.description || 'No description'}</span>
                                    <span class="threat-risk">${threat.risk_level || 'medium'}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';

        const section = document.querySelector('#threat-results .result-section:nth-child(4)');
        if (section) {
            section.innerHTML = html;
        }
    }

    getPathRiskLevel(score) {
        if (score >= 80) return 'critical';
        if (score >= 60) return 'high';
        if (score >= 40) return 'medium';
        return 'low';
    }

    displayMitigationStrategies(mitigations) {
        if (!mitigations || !Array.isArray(mitigations) || mitigations.length === 0) {
            // Show placeholder if no mitigations
            const section = document.querySelector('#threat-results .result-section:nth-child(5)');
            if (section) {
                section.innerHTML = `
                    <h3><i class="fas fa-shield-alt"></i> Mitigation Strategies</h3>
                    <div class="data-not-available">
                        <i class="fas fa-info-circle"></i>
                        <h4>No Specific Mitigations</h4>
                        <p>No specific mitigation strategies were generated. Review the threat analysis for security recommendations.</p>
                    </div>
                `;
            }
            return;
        }

        let html = '<h3><i class="fas fa-shield-alt"></i> Mitigation Strategies</h3>';
        html += '<div class="mitigation-grid">';
        
        mitigations.forEach(mitigation => {
            html += `
                <div class="mitigation-card">
                    <div class="mitigation-header">
                        <i class="fas fa-${this.getMitigationIcon(mitigation.category)}"></i>
                        <h4>${mitigation.title || 'Security Control'}</h4>
                    </div>
                    <div class="mitigation-content">
                        <p>${mitigation.description || 'No description available'}</p>
                        <div class="mitigation-meta">
                            <span class="priority priority-${mitigation.priority || 'medium'}">${mitigation.priority || 'medium'}</span>
                            <span class="effort">Effort: ${mitigation.effort || 'medium'}</span>
                            <span class="category">${mitigation.category || 'general'}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';

        const section = document.querySelector('#threat-results .result-section:nth-child(5)');
        if (section) {
            section.innerHTML = html;
        }
    }

    getMitigationIcon(category) {
        const icons = {
            'network': 'network-wired',
            'access': 'user-shield',
            'data': 'database',
            'application': 'code',
            'monitoring': 'chart-line',
            'default': 'shield-alt'
        };
        return icons[category] || icons.default;
    }

    displayEnhancedRecommendations(recommendations) {
        if (!recommendations || !Array.isArray(recommendations) || recommendations.length === 0) {
            // Show placeholder if no recommendations
            const section = document.querySelector('#threat-results .result-section:last-child');
            if (section) {
                section.innerHTML = `
                    <h3><i class="fas fa-road"></i> Enhanced Recommendations</h3>
                    <div class="data-not-available">
                        <i class="fas fa-info-circle"></i>
                        <h4>No Specific Recommendations</h4>
                        <p>No specific recommendations were generated. Review the threat analysis and mitigation strategies for guidance.</p>
                    </div>
                `;
            }
            return;
        }

        let html = '<h3><i class="fas fa-road"></i> Enhanced Recommendations</h3>';
        html += '<div class="recommendations-timeline-enhanced">';
        
        const priorityGroups = {
            'critical': recommendations.filter(r => r.priority === 'critical'),
            'high': recommendations.filter(r => r.priority === 'high'),
            'medium': recommendations.filter(r => r.priority === 'medium'),
            'low': recommendations.filter(r => r.priority === 'low')
        };

        for (const [priority, recs] of Object.entries(priorityGroups)) {
            if (recs.length > 0) {
                html += `
                    <div class="timeline-phase ${priority}">
                        <h4>${this.capitalizeFirstLetter(priority)} Priority Actions</h4>
                        <div class="action-list">
                            ${recs.map(rec => `
                                <div class="action-item">
                                    <i class="fas fa-${this.getRecommendationIcon(rec.category)}"></i>
                                    <div class="action-content">
                                        <span class="action-desc">${rec.description || 'No description'}</span>
                                        <div class="action-meta">
                                            <span class="action-timeframe">${rec.timeframe || 'Not specified'}</span>
                                            <span class="action-category">${rec.category || 'general'}</span>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        }
        
        html += '</div>';

        const section = document.querySelector('#threat-results .result-section:last-child');
        if (section) {
            section.innerHTML = html;
        }
    }

    displayOWASPThreats(owaspThreats) {
        if (!owaspThreats || owaspThreats.length === 0) return '';

        return `
            <div class="analysis-section">
                <h4>OWASP Top 10</h4>
                <div class="owasp-grid">
                    ${owaspThreats.map(threat => {
                        const description = this.createTruncatedText(threat.description, 600); // 600 chars
                        
                        return `
                            <div class="owasp-item owasp-${threat.risk_level}">
                                <div class="owasp-header">
                                    <h5>${threat.risk_id}: ${threat.name}</h5>
                                    <span class="owasp-category">${threat.category}</span>
                                </div>
                                <div class="owasp-details">
                                    <div class="owasp-description">
                                        ${description}
                                    </div>
                                    <p><strong>Components:</strong> ${threat.applicable_components.join(', ')}</p>
                                    <div class="owasp-mitigation">
                                        <strong>Mitigation:</strong>
                                        <ul>
                                            ${threat.mitigation.map(m => `<li>${m}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    calculateRiskAssessment(threats) {
        const threatCounts = {
            'critical': 0,
            'high': 0,
            'medium': 0,
            'low': 0
        };
        
        let totalRiskScore = 0;
        let threatCount = 0;

        // Count threats from ALL frameworks and methodologies
        this.countThreatsFromAllSources(threats, threatCounts);

        // Calculate total counts and risk scores
        threatCount = threatCounts.critical + threatCounts.high + threatCounts.medium + threatCounts.low;
        
        // Calculate weighted risk score based on threat counts
        totalRiskScore += threatCounts.critical * 100;
        totalRiskScore += threatCounts.high * 75;
        totalRiskScore += threatCounts.medium * 50;
        totalRiskScore += threatCounts.low * 25;
        
        const overallRiskScore = threatCount > 0 ? Math.min(100, Math.round(totalRiskScore / threatCount)) : 0;
        
        return {
            'overall_risk_score': overallRiskScore,
            'threat_counts': threatCounts,
            'total_threats': threatCount
        };
    }

    // New method to count threats from all sources
    countThreatsFromAllSources(threats, threatCounts) {
        if (!threats) return;

        // STRIDE Analysis
        if (threats.stride && typeof threats.stride === 'object') {
            Object.values(threats.stride).forEach(threatList => {
                if (Array.isArray(threatList)) {
                    threatList.forEach(threat => {
                        if (threat && threat.risk_level) {
                            this.incrementThreatCount(threatCounts, threat.risk_level);
                        }
                    });
                }
            });
        }

        // DREAD Analysis
        if (Array.isArray(threats.dread)) {
            threats.dread.forEach(threat => {
                if (threat && threat.risk_level) {
                    this.incrementThreatCount(threatCounts, threat.risk_level);
                }
            });
        }

        // MITRE ATT&CK
        if (threats.mitre && typeof threats.mitre === 'object') {
            Object.values(threats.mitre).forEach(technique => {
                if (technique && technique.risk_level) {
                    this.incrementThreatCount(threatCounts, technique.risk_level);
                }
            });
        }

        // OWASP Analysis
        if (Array.isArray(threats.owasp)) {
            threats.owasp.forEach(threat => {
                if (threat && threat.risk_level) {
                    this.incrementThreatCount(threatCounts, threat.risk_level);
                }
            });
        }

        // CWE Analysis
        if (Array.isArray(threats.cwe)) {
            threats.cwe.forEach(weakness => {
                if (weakness && weakness.risk_level) {
                    this.incrementThreatCount(threatCounts, weakness.risk_level);
                }
            });
        }

        // AI Discovered Threats
        const aiThreats = threats.ai_discovered || threats.ai_threats || threats.ai_discovered_threats;
        if (Array.isArray(aiThreats)) {
            aiThreats.forEach(threat => {
                if (threat && threat.risk_level) {
                    this.incrementThreatCount(threatCounts, threat.risk_level);
                }
            });
        }

        // NIST Analysis
        if (Array.isArray(threats.nist)) {
            threats.nist.forEach(control => {
                if (control && control.risk_level) {
                    this.incrementThreatCount(threatCounts, control.risk_level);
                }
            });
        }

        // CIS Analysis
        if (Array.isArray(threats.cis)) {
            threats.cis.forEach(control => {
                if (control && control.risk_level) {
                    this.incrementThreatCount(threatCounts, control.risk_level);
                }
            });
        }

        // ISO 27001 Analysis
        if (Array.isArray(threats.iso27001)) {
            threats.iso27001.forEach(control => {
                if (control && control.risk_level) {
                    this.incrementThreatCount(threatCounts, control.risk_level);
                }
            });
        }
    }

    // Helper method to safely increment threat counts
    incrementThreatCount(threatCounts, riskLevel) {
        if (threatCounts[riskLevel] !== undefined) {
            threatCounts[riskLevel]++;
        } else {
            // If risk level is unknown, default to medium
            threatCounts.medium++;
        }
    }

    // Update the calculateEnhancedRiskAssessment method (remove 'private')
    calculateEnhancedRiskAssessment(threats, systemData) {
        const baseAssessment = this.calculateRiskAssessment(threats);
        
        const pathRisks = [];
        if (threats.attack_paths && Array.isArray(threats.attack_paths)) {
            threats.attack_paths.forEach(path => {
                if (path && typeof path === 'object' && path.risk_score !== undefined) {
                    pathRisks.push(path.risk_score);
                }
            });
        }
        
        baseAssessment.attack_path_risk = pathRisks.length > 0 ? 
            Math.round(pathRisks.reduce((sum, score) => sum + score, 0) / pathRisks.length) : 0;
        
        baseAssessment.complexity_factor = this.calculateSystemComplexity(systemData);
        baseAssessment.overall_risk_score = this.calculateOverallEnhancedRisk(baseAssessment);
        
        return baseAssessment;
    }

    // Make sure calculateSystemComplexity method exists
    calculateSystemComplexity(systemData) {
        const componentCount = systemData.components ? systemData.components.length : 0;
        const flowCount = systemData.data_flows ? systemData.data_flows.length : 0;
        
        const complexity = (componentCount * 0.4) + (flowCount * 0.6);
        return Math.min(1.0, complexity / 10);
    }

    // Make sure calculateOverallEnhancedRisk method exists
    calculateOverallEnhancedRisk(assessment) {
        if (typeof assessment !== 'object') return 0;
        
        const baseScore = assessment.overall_risk_score || 0;
        const pathRisk = assessment.attack_path_risk || 0;
        const complexity = (assessment.complexity_factor || 0) * 100;
        
        return Math.round((baseScore * 0.6) + (pathRisk * 0.3) + (complexity * 0.1));
    }

    getRecommendationIcon(category) {
        const icons = {
            'network_security': 'network-wired',
            'emerging_threats': 'robot',
            'access_control': 'user-shield',
            'data_protection': 'database',
            'default': 'shield-alt'
        };
        return icons[category] || icons.default;
    }

    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    async loadAnalysis(uuid) {
        try {
            this.showLoading();
            
            const formData = new FormData();
            formData.append('tool', 'threat_modeling');
            formData.append('action', 'load_analysis');
            formData.append('analysis_uuid', uuid);

            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const results = await response.json();

            if (results.success) {
                this.loadAnalysisData(results.data);
                this.showNotification('Analysis loaded successfully', 'success');
            } else {
                throw new Error(results.error || 'Failed to load analysis');
            }

        } catch (error) {
            console.error('Load analysis error:', error);
            this.showError('Failed to load analysis: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    saveDraft() {
        this.showNotification('Draft saving functionality coming soon!', 'info');
        // TODO: Implement draft saving logic
    }

    validateModel() {
        const validationResults = this.validateSystemModel();
        if (validationResults.isValid) {
            this.showNotification('System model validation passed!', 'success');
        } else {
            this.showNotification(`Validation issues: ${validationResults.issues.join(', ')}`, 'warning');
        }
    }

    validateSystemModel() {
        const issues = [];
        
        // Basic validation rules
        if (!this.systemData.name) {
            issues.push('System name is required');
        }
        
        if (this.systemData.components.length === 0) {
            issues.push('At least one component is required');
        }
        
        // Check for isolated components (no connections)
        const connectedComponents = new Set();
        this.systemData.data_flows.forEach(flow => {
            connectedComponents.add(flow.sourceId);
            connectedComponents.add(flow.destinationId);
        });
        
        this.systemData.components.forEach(component => {
            if (!connectedComponents.has(component.id)) {
                issues.push(`Component "${component.name}" has no connections`);
            }
        });
        
        return {
            isValid: issues.length === 0,
            issues: issues
        };
    }

    generateReport() {
        this.showNotification('Report generation functionality coming soon!', 'info');
        // TODO: Implement PDF report generation
    }

    loadAnalysisData(analysisData) {
        // Clear current data
        this.systemData = {
            name: analysisData.analysis.system_name,
            type: analysisData.analysis.system_type,
            components: [],
            data_flows: [],
            methodologies: ['stride', 'dread'],
            frameworks: ['owasp']
        };

        // Clear canvas
        document.getElementById('flow-canvas').innerHTML = '';

        // Load components
        analysisData.components.forEach(component => {
            this.systemData.components.push({
                id: component.id,
                name: component.component_name,
                type: component.component_type,
                sensitivity: component.sensitivity,
                description: component.description,
                position: { x: component.position_x, y: component.position_y }
            });
            this.renderComponentOnCanvas(this.systemData.components[this.systemData.components.length - 1]);
        });

        // Load data flows
        analysisData.data_flows.forEach(flow => {
            this.systemData.data_flows.push({
                id: flow.id,
                source: flow.source_component,
                sourceId: this.findComponentIdByName(flow.source_component),
                destination: flow.destination_component,
                destinationId: this.findComponentIdByName(flow.destination_component),
                data_type: flow.data_type,
                protocol: flow.protocol
            });
            this.renderConnection(this.systemData.data_flows[this.systemData.data_flows.length - 1]);
        });

        // Update system name
        document.getElementById('system-name').value = this.systemData.name;
        document.getElementById('system-type').value = this.systemData.type;

        this.showNotification('Analysis data loaded', 'success');
    }

    findComponentIdByName(name) {
        const component = this.systemData.components.find(c => c.name === name);
        return component ? component.id : null;
    }

    // Helper method to truncate text and add read more functionality
    createTruncatedText(text, maxLength = 600) {
        if (!text) return '<div class="full-text">No description available</div>';
        
        // Clean the text first
        const cleanText = this.escapeHtml(text.trim());
        
        // For AI content, be more generous with length limits
        const aiMaxLength = 1200; // Double the limit for AI content
        
        if (cleanText.length <= aiMaxLength) {
            return `<div class="full-text">${cleanText}</div>`;
        }

        const randomId = 'text-' + Math.random().toString(36).substr(2, 9);
        
        return `
            <div class="truncated-text" id="${randomId}">
                ${cleanText}
            </div>
            <button class="read-more-btn" data-target="${randomId}">
                <span>Read more</span>
                <i class="fas fa-chevron-down"></i>
            </button>
        `;
    }

    // Escape HTML to prevent XSS
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Initialize read more functionality
    initReadMoreButtons() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.read-more-btn')) {
                const button = e.target.closest('.read-more-btn');
                const targetId = button.getAttribute('data-target');
                const textContainer = document.getElementById(targetId);
                const threatItem = textContainer.closest('.threat-item, .dread-item, .mitre-item, .owasp-item, .cwe-item, .ai-threat-item');
                
                if (textContainer) {
                    const isExpanded = textContainer.classList.contains('expanded');
                    
                    if (isExpanded) {
                        // Collapse
                        textContainer.classList.remove('expanded');
                        button.classList.remove('expanded');
                        button.innerHTML = '<span>Read more</span><i class="fas fa-chevron-down"></i>';
                        if (threatItem) {
                            threatItem.classList.remove('expanded');
                        }
                    } else {
                        // Expand - show ALL remaining text
                        textContainer.classList.add('expanded');
                        button.classList.add('expanded');
                        button.innerHTML = '<span>Read less</span><i class="fas fa-chevron-up"></i>';
                        if (threatItem) {
                            threatItem.classList.add('expanded');
                        }
                    }
                }
            }
        });
    }
}

// Initialize enhanced tool
document.addEventListener('DOMContentLoaded', function() {
    window.threatModeler = new ThreatModelingTool();
});

const enhancedAICSS = `
    .ai-threats-grid {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .ai-threat-item {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .ai-threat-item:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .ai-threat-header {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .ai-threat-title {
        flex: 1;
    }

    .ai-threat-title h5 {
        margin: 0 0 0.5rem 0;
        font-size: 1.1rem;
        color: var(--text-color);
        font-weight: 600;
    }

    .ai-threat-meta {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .confidence {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .confidence.high {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
    }

    .confidence.medium {
        background: rgba(234, 179, 8, 0.1);
        color: #ca8a04;
    }

    .confidence.low {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .threat-risk {
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .ai-threat-category {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .ai-threat-content {
        margin: 1.5rem 0;
    }

    .ai-threat-description {
        margin-bottom: 1.5rem;
    }

    .description-content {
        line-height: 1.6;
        color: var(--text-color);
    }

    .description-content strong {
        color: var(--primary-color);
        font-weight: 600;
    }

    .description-content em {
        font-style: italic;
        color: var(--text-muted);
    }

    .description-content code {
        background: rgba(59, 130, 246, 0.1);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
        color: #3b82f6;
    }

    .ai-threat-details-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    @media (min-width: 768px) {
        .ai-threat-details-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .detail-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .detail-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--text-color);
    }

    .detail-value {
        padding-left: 1.75rem;
        color: var(--text-muted);
        line-height: 1.5;
    }

    .mitigation-list {
        margin: 0;
        padding-left: 1rem;
    }

    .mitigation-list li {
        margin-bottom: 0.5rem;
        line-height: 1.5;
    }

    .mitigation-list li:last-child {
        margin-bottom: 0;
    }

    .ai-threat-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        margin-top: 1rem;
    }

    .ai-threat-source {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-muted);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .domain-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .truncated-description {
        position: relative;
    }

    .read-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: none;
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        margin-top: 1rem;
        transition: all 0.3s ease;
    }

    .read-more-btn:hover {
        background: var(--primary-color);
        color: white;
    }

    .read-more-btn.expanded {
        background: var(--primary-color);
        color: white;
    }

    .no-description {
        color: var(--text-muted);
        font-style: italic;
        padding: 1rem;
        text-align: center;
        background: rgba(0,0,0,0.02);
        border-radius: 6px;
    }

    .ai-analysis-info {
        margin-bottom: 1.5rem;
    }

    .ai-info-banner {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: 8px;
        color: var(--text-color);
    }

    .ai-info-banner i {
        color: #3b82f6;
        font-size: 1.2rem;
    }

    /* Risk level specific styling */
    .ai-threat-item.threat-critical {
        border-left: 4px solid #dc2626;
    }

    .ai-threat-item.threat-high {
        border-left: 4px solid #ea580c;
    }

    .ai-threat-item.threat-medium {
        border-left: 4px solid #ca8a04;
    }

    .ai-threat-item.threat-low {
        border-left: 4px solid #16a34a;
    }

    .risk-critical { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .risk-high { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .risk-medium { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
    .risk-low { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    `;

    // Add the CSS to the document
    function addEnhancedAICSS() {
        if (!document.getElementById('enhanced-ai-css')) {
            const style = document.createElement('style');
            style.id = 'enhanced-ai-css';
            style.textContent = enhancedAICSS;
            document.head.appendChild(style);
        }
    }

    // Call this when initializing the threat modeler
    addEnhancedAICSS();