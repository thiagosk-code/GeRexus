document.addEventListener('DOMContentLoaded', () => {
    const config = window.partidaData || {};
    const textos = config.textos || {};
    let estado = config.estadoInicial || null;
    let version = estado?.version || 0;
    let indiceTablero = 0;
    let recintoAbierto = null;
    let consultando = false;
    let forzarPendiente = false;
    let accionando = false;
    let temporizador = null;
    let fallos = 0;
    let diferenciaServidorMs = 0;
    let indiceInicializado = false;
    let errorRecinto = '';
    let ultimoControlPanel = null;

    const elementos = {
        jugadores: document.getElementById('partida-jugadores'),
        registro: document.getElementById('partida-registro'),
        propietario: document.getElementById('tablero-propietario'),
        mapa: document.getElementById('mapa-imagen'),
        mano: document.getElementById('partida-mano'),
        activo: document.getElementById('turno-activo'),
        restriccion: document.getElementById('dado-restriccion'),
        contador: document.getElementById('cuenta-regresiva'),
        ronda: document.getElementById('partida-ronda'),
        turno: document.getElementById('partida-turno'),
        tirar: document.getElementById('btn-tirar-dado'),
        decision: document.getElementById('estado-decision'),
        error: document.getElementById('partida-error'),
        modal: document.getElementById('modal-recinto-partida'),
        modalTitulo: document.getElementById('modal-recinto-titulo'),
        modalRegla: document.getElementById('modal-recinto-regla'),
        modalColocados: document.getElementById('modal-recinto-colocados'),
        modalMano: document.getElementById('modal-recinto-mano'),
        modalSeleccion: document.getElementById('modal-seleccion-mano'),
        modalMensaje: document.getElementById('modal-recinto-mensaje'),
        modalFinal: document.getElementById('modal-final-partida'),
        clasificacion: document.getElementById('clasificacion-final'),
        anterior: document.getElementById('tablero-anterior'),
        siguiente: document.getElementById('tablero-siguiente'),
        panelJugadores: document.getElementById('panel-jugadores-partida'),
        panelRegistro: document.getElementById('panel-registro-partida'),
        abrirJugadores: document.getElementById('abrir-panel-jugadores'),
        abrirRegistro: document.getElementById('abrir-panel-registro'),
        panelBackdrop: document.getElementById('partida-panel-backdrop'),
        votacion: document.getElementById('votacion-finalizacion'),
        votosEstado: document.getElementById('votos-finalizacion-estado'),
        votarFinalizacion: document.getElementById('btn-votar-finalizacion')
    };

    const t = (clave, fallback = '') => textos[clave] || fallback || clave;
    const vistaPanelMovil = window.matchMedia('(max-width: 720px)');
    const reemplazar = (plantilla, valores) => Object.entries(valores).reduce(
        (salida, [clave, valor]) => salida.replaceAll(`{${clave}}`, String(valor)), plantilla
    );
    const rutaAsset = (ruta) => /^[A-Za-z0-9._-]+$/.test(ruta || '')
        ? `../../Assets/${ruta}` : '../../Assets/sinAdepto.png';

    function jugadorActualDelTablero() {
        const jugadores = estado?.jugadores || [];
        return jugadores[indiceTablero] || jugadores[0] || null;
    }

    function nombreRecinto(codigo) {
        const definicion = (estado?.recintos || []).find((r) => r.codigo === codigo);
        return definicion ? t(definicion.nombre_key, definicion.nombre_default) : codigo;
    }

    function reglaRecinto(codigo) {
        const definicion = (estado?.recintos || []).find((r) => r.codigo === codigo);
        return definicion ? t(definicion.regla_key, definicion.regla_default) : '';
    }

    function crearFicha(ficha, interactiva = false, onClick = null) {
        const nodo = document.createElement(interactiva ? 'button' : 'span');
        nodo.className = 'ficha-partida';
        if (ficha.pendiente) nodo.classList.add('pendiente');
        if (interactiva) nodo.type = 'button';

        const img = document.createElement('img');
        img.src = rutaAsset(ficha.ruta);
        img.alt = ficha.especie || t('adepto', 'Adepto');
        img.loading = 'lazy';
        nodo.appendChild(img);
        nodo.title = ficha.especie || '';
        if (interactiva && onClick) nodo.addEventListener('click', onClick);
        return nodo;
    }

    function renderJugadores() {
        elementos.jugadores.replaceChildren();
        const propietario = jugadorActualDelTablero();
        (estado.jugadores || []).forEach((jugador) => {
            const li = document.createElement('li');
            if (jugador.id_usuario === estado.turno?.id_activo) li.classList.add('activo');
            if (jugador.id_usuario === propietario?.id_usuario) li.classList.add('tablero-visible');
            const nombre = document.createElement('span');
            nombre.textContent = jugador.nombre;
            const puntos = document.createElement('strong');
            puntos.textContent = `${jugador.puntos} p`;
            const detalle = document.createElement('span');
            detalle.className = 'jugador-detalle';
            if (jugador.voto_finalizacion) {
                const voto = document.createElement('span');
                voto.className = 'jugador-voto-finalizacion';
                voto.textContent = '✓';
                voto.title = t('voto_finalizacion_marcado', 'Votó finalizar');
                voto.setAttribute('aria-label', voto.title);
                detalle.appendChild(voto);
            }
            detalle.appendChild(puntos);
            li.append(nombre, detalle);
            elementos.jugadores.appendChild(li);
        });
    }

    function renderVotacion() {
        const votacion = estado?.votacion_finalizacion;
        const visible = Boolean(votacion?.habilitada);
        elementos.votacion.hidden = !visible;
        if (!visible) return;

        elementos.votosEstado.textContent = reemplazar(
            t('votos_finalizacion_estado', 'Votos para finalizar: {votos}/{requeridos}'),
            { votos: votacion.votos, requeridos: votacion.requeridos }
        );
        elementos.votarFinalizacion.textContent = votacion.mi_voto
            ? t('retirar_voto_finalizacion', 'Retirar voto')
            : t('votar_finalizacion', 'Votar finalización');
        elementos.votarFinalizacion.disabled = accionando;
    }

    function renderTablero() {
        const propietario = jugadorActualDelTablero();
        if (!propietario) return;
        elementos.propietario.textContent = propietario.nombre;
        elementos.mapa.src = rutaAsset(estado.partida?.imagen_tablero || 'MapaAsistente.png');
        elementos.mapa.onerror = () => { elementos.mapa.src = '../../Assets/MapaAsistente.png'; };
        const tablero = estado.tableros?.[String(propietario.id_usuario)] || {};

        document.querySelectorAll('.recinto-partida').forEach((hotspot) => {
            const codigo = hotspot.dataset.recinto || '';
            const contenedor = hotspot.querySelector('.recinto-fichas');
            contenedor.replaceChildren();
            (tablero[codigo] || []).forEach((ficha) => {
                const img = document.createElement('img');
                img.src = rutaAsset(ficha.ruta);
                img.alt = '';
                if (ficha.pendiente) img.classList.add('pendiente');
                contenedor.appendChild(img);
            });
        });
        renderJugadores();
        if (recintoAbierto) renderModalRecinto();
    }

    function renderMano() {
        elementos.mano.replaceChildren();
        (estado.mano || []).forEach((ficha) => elementos.mano.appendChild(crearFicha(ficha)));
        elementos.decision.textContent = estado.puede_colocar
            ? t('esperando_jugadores', 'Elegí un recinto y una ficha.')
            : (estado.turno?.fase === 'colocacion' ? t('decision_confirmada', 'Decisión confirmada.') : '');
    }

    function renderTurno() {
        const turno = estado.turno;
        if (!turno) return;
        const activo = (estado.jugadores || []).find((j) => j.id_usuario === turno.id_activo);
        elementos.activo.textContent = `${t('dado_de', 'Dado de')}: ${activo?.nombre || ''}`;
        elementos.ronda.textContent = `${t('ronda', 'Ronda')} ${turno.ronda}`;
        elementos.turno.textContent = `${t('turno', 'Turno')} ${turno.numero}`;
        elementos.tirar.hidden = !estado.puede_tirar;
        elementos.restriccion.textContent = turno.restriccion
            ? t(`restriccion_${turno.restriccion}`, turno.restriccion)
            : t('esperando_tirada', 'Esperando la tirada del dado');
    }

    function renderRegistro() {
        elementos.registro.replaceChildren();
        (estado.registro || []).forEach((evento) => {
            const li = document.createElement('li');
            if (evento.tipo === 'activo') {
                li.textContent = reemplazar(t('registro_activo', '{nombre} es el jugador activo.'), { nombre: evento.nombre });
            } else if (evento.tipo === 'dado') {
                li.textContent = reemplazar(t('registro_dado', 'Restricción: {restriccion}.'), {
                    restriccion: t(`restriccion_${evento.restriccion}`, evento.restriccion)
                });
            } else {
                li.textContent = reemplazar(t('registro_colocacion', '{nombre} coloca {adepto} en {recinto}.'), {
                    nombre: evento.nombre, adepto: evento.adepto, recinto: nombreRecinto(evento.recinto_codigo)
                });
                if (evento.automatica) li.textContent += ` ${t('registro_automatica', '(automática)')}`;
            }
            elementos.registro.appendChild(li);
        });
        elementos.registro.scrollTop = elementos.registro.scrollHeight;
    }

    function renderModalRecinto() {
        if (!recintoAbierto) return;
        const propietario = jugadorActualDelTablero();
        const tablero = estado.tableros?.[String(propietario.id_usuario)] || {};
        elementos.modalTitulo.textContent = nombreRecinto(recintoAbierto);
        elementos.modalRegla.textContent = reglaRecinto(recintoAbierto);
        elementos.modalColocados.replaceChildren();
        (tablero[recintoAbierto] || []).forEach((ficha) => elementos.modalColocados.appendChild(crearFicha(ficha)));

        const esPropio = propietario.id_usuario === Number(config.idUsuario);
        const puedeElegir = esPropio && estado.puede_colocar;
        elementos.modalSeleccion.hidden = !puedeElegir;
        elementos.modalMano.replaceChildren();
        elementos.modalMensaje.classList.toggle('error', errorRecinto !== '');
        elementos.modalMensaje.textContent = errorRecinto || (esPropio ? '' : t('tablero_ajeno', 'Este tablero es de solo lectura.'));
        if (puedeElegir) {
            (estado.mano || []).forEach((ficha) => {
                elementos.modalMano.appendChild(crearFicha(ficha, true, () => colocar(ficha.id_ficha, recintoAbierto)));
            });
        }
    }

    function renderFinal() {
        if (estado.partida?.estado !== 'finalizada') return;
        elementos.clasificacion.replaceChildren();
        [...(estado.jugadores || [])].sort((a, b) => (a.puesto - b.puesto) || (b.puntos - a.puntos)).forEach((jugador) => {
            const li = document.createElement('li');
            li.textContent = `${jugador.puesto}. ${jugador.nombre} — ${jugador.puntos} ${t('puntos', 'puntos')}`;
            if (jugador.ganador) li.classList.add('ganador');
            elementos.clasificacion.appendChild(li);
        });
        elementos.modalFinal.classList.add('active');
    }

    function render() {
        if (!estado?.partida) return;
        const propio = (estado.jugadores || []).findIndex((j) => j.id_usuario === Number(config.idUsuario));
        if (!indiceInicializado) {
            indiceTablero = Math.max(0, propio);
            indiceInicializado = true;
        } else if (indiceTablero < 0 || indiceTablero >= estado.jugadores.length) {
            indiceTablero = Math.max(0, propio);
        }
        if (estado.servidor_epoch) diferenciaServidorMs = estado.servidor_epoch * 1000 - Date.now();
        renderTablero();
        renderVotacion();
        renderMano();
        renderTurno();
        renderRegistro();
        renderFinal();
    }

    async function enviar(url, campos) {
        const cuerpo = new URLSearchParams({
            ...campos,
            id_partida: String(config.idPartida),
            csrf_token: config.csrfToken
        });
        const respuesta = await fetch(url, {
            method: 'POST', credentials: 'same-origin', cache: 'no-store',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body: cuerpo.toString()
        });
        const data = await respuesta.json().catch(() => ({ exito: false, mensaje_key: 'err_cargar' }));
        if (!respuesta.ok || data.exito === false) throw new Error(data.mensaje_key || 'err_cargar');
        return data;
    }

    async function ejecutarAccion(campos) {
        if (accionando) return;
        accionando = true;
        renderVotacion();
        elementos.error.textContent = '';
        errorRecinto = '';
        try {
            await enviar(config.accionesUrl, campos);
            cerrarModal();
            await sincronizar(true);
        } catch (error) {
            const mensaje = t(error.message, t('err_cargar', 'No se pudo completar la acción.'));
            await sincronizar(true);
            if (recintoAbierto && elementos.modal.classList.contains('active')) {
                errorRecinto = mensaje;
                renderModalRecinto();
            } else {
                elementos.error.textContent = mensaje;
            }
        } finally {
            accionando = false;
            renderVotacion();
        }
    }

    const colocar = (idFicha, recinto) => ejecutarAccion({ accion: 'colocar', id_ficha: String(idFicha), recinto });

    async function sincronizar(forzar = false) {
        if (consultando) {
            if (forzar) forzarPendiente = true;
            return;
        }
        consultando = true;
        clearTimeout(temporizador);
        let espera = document.hidden ? 10000 : 2000;
        try {
            const data = await enviar(config.estadoUrl, { version: String(forzar ? 0 : version) });
            fallos = 0;
            if (data.cambio) {
                estado = data;
                version = data.version;
                render();
            } else if (data.servidor_epoch) {
                diferenciaServidorMs = data.servidor_epoch * 1000 - Date.now();
                if (estado?.turno && data.limite_epoch !== undefined) {
                    estado.turno.limite_epoch = data.limite_epoch;
                }
            }
        } catch (error) {
            fallos++;
            espera = Math.min(15000, 2000 * (fallos + 1));
            elementos.error.textContent = t(error.message, t('err_cargar', 'No se pudo sincronizar.'));
        } finally {
            consultando = false;
            const repetirForzado = forzarPendiente;
            forzarPendiente = false;
            temporizador = setTimeout(() => sincronizar(repetirForzado), repetirForzado ? 0 : espera);
        }
    }

    function actualizarContador() {
        const limiteEpoch = estado?.turno?.limite_epoch;
        if (!limiteEpoch || estado?.partida?.estado !== 'en_juego') {
            elementos.contador.textContent = '';
            return;
        }
        const ahoraServidor = Date.now() + diferenciaServidorMs;
        const segundos = Math.max(0, Math.ceil((limiteEpoch * 1000 - ahoraServidor) / 1000));
        elementos.contador.textContent = reemplazar(t('segundos_restantes', '{segundos} s'), { segundos });
    }

    function abrirModal(codigo) {
        recintoAbierto = codigo;
        errorRecinto = '';
        renderModalRecinto();
        elementos.modal.classList.add('active');
    }
    function cerrarModal() {
        recintoAbierto = null;
        errorRecinto = '';
        elementos.modal.classList.remove('active');
    }

    function cerrarPanelMovil(devolverFoco = true) {
        elementos.panelJugadores.classList.remove('abierto');
        elementos.panelRegistro.classList.remove('abierto');
        elementos.panelBackdrop.classList.remove('active');
        elementos.abrirJugadores.setAttribute('aria-expanded', 'false');
        elementos.abrirRegistro.setAttribute('aria-expanded', 'false');
        if (vistaPanelMovil.matches) {
            elementos.panelJugadores.setAttribute('aria-hidden', 'true');
            elementos.panelRegistro.setAttribute('aria-hidden', 'true');
            elementos.panelJugadores.inert = true;
            elementos.panelRegistro.inert = true;
        }
        if (devolverFoco && ultimoControlPanel) ultimoControlPanel.focus();
        ultimoControlPanel = null;
    }

    function abrirPanelMovil(panel, control) {
        cerrarPanelMovil(false);
        ultimoControlPanel = control;
        panel.classList.add('abierto');
        panel.setAttribute('aria-hidden', 'false');
        panel.inert = false;
        elementos.panelBackdrop.classList.add('active');
        control.setAttribute('aria-expanded', 'true');
        panel.querySelector('[data-close-game-panel]')?.focus();
    }

    function ajustarPanelesAlViewport() {
        if (vistaPanelMovil.matches) {
            cerrarPanelMovil(false);
            return;
        }
        cerrarPanelMovil(false);
        elementos.panelJugadores.setAttribute('aria-hidden', 'false');
        elementos.panelRegistro.setAttribute('aria-hidden', 'false');
        elementos.panelJugadores.inert = false;
        elementos.panelRegistro.inert = false;
    }

    document.querySelectorAll('.recinto-partida').forEach((boton) => boton.addEventListener('click', () => abrirModal(boton.dataset.recinto)));
    document.querySelectorAll('[data-close-modal]').forEach((boton) => boton.addEventListener('click', cerrarModal));
    elementos.modal.addEventListener('click', (event) => { if (event.target === elementos.modal) cerrarModal(); });
    elementos.tirar.addEventListener('click', () => ejecutarAccion({ accion: 'tirar_dado' }));
    elementos.votarFinalizacion.addEventListener('click', () => {
        const miVoto = Boolean(estado?.votacion_finalizacion?.mi_voto);
        ejecutarAccion({ accion: 'establecer_voto_finalizacion', voto: miVoto ? '0' : '1' });
    });
    function cambiarTablero(desplazamiento) {
        const jugadores = estado?.jugadores || [];
        if (jugadores.length < 2) return;
        indiceTablero = (indiceTablero + desplazamiento + jugadores.length) % jugadores.length;
        recintoAbierto = null;
        renderTablero();
    }

    elementos.anterior.addEventListener('click', (event) => {
        event.preventDefault();
        cambiarTablero(-1);
    });
    elementos.siguiente.addEventListener('click', (event) => {
        event.preventDefault();
        cambiarTablero(1);
    });
    elementos.abrirJugadores.addEventListener('click', () => abrirPanelMovil(elementos.panelJugadores, elementos.abrirJugadores));
    elementos.abrirRegistro.addEventListener('click', () => abrirPanelMovil(elementos.panelRegistro, elementos.abrirRegistro));
    elementos.panelBackdrop.addEventListener('click', () => cerrarPanelMovil());
    document.querySelectorAll('[data-close-game-panel]').forEach((boton) => boton.addEventListener('click', () => cerrarPanelMovil()));
    vistaPanelMovil.addEventListener('change', ajustarPanelesAlViewport);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) sincronizar(true); });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (elementos.panelBackdrop.classList.contains('active')) cerrarPanelMovil();
        else cerrarModal();
    });

    ajustarPanelesAlViewport();
    render();
    setInterval(actualizarContador, 250);
    temporizador = setTimeout(sincronizar, 2000);
});
