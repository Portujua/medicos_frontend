;(() => {
	class ChatController {
		constructor($scope, $timeout, RESTService, LoginService) {
			this.$scope = $scope;
			this.$timeout = $timeout;
			this.medico = this.$scope.medico;
			this.paciente = this.$scope.paciente;
			this.RESTService = RESTService;
			this.session = LoginService;
			this.mensajes = [];

			let self = this;

			$scope.$watch('paciente', (paciente) => {
        self.paciente = paciente;
        self.reset();
      })

      $scope.$watch('medico', (medico) => {
        self.medico = medico;
        self.reset();
      })
		}

		load(last = -1, reset = false) {
			if (this.checkChatters()) {
				this.RESTService.get('mensajes', {
					medico: this.medico.id, 
					paciente: this.paciente.id,
					n: 10,
					last: last,
					me: this.session.getCurrentUser().usuario,
					es_medico: this.session.getCurrentUser().es_medico
				}).then((response) => {
					this.concatMessages(response.data.mensajes, true);

					this.scrollDown();

					if (reset) {
						this.tick();
					}
				})
			}
		}

		loadNewMessages() {
			this.RESTService.get('mensajes', {
					medico: this.medico.id, 
					paciente: this.paciente.id,
					n: 10,
					last: -1,
					nuevos: true,
					me: this.session.getCurrentUser().usuario,
					es_medico: this.session.getCurrentUser().es_medico
				}).then((response) => {
					this.concatMessages(response.data.mensajes);
					this.scrollDown();
				})
		}

		loadSeenMessages() {
			let messages = '';

			for (let i = 0; i < this.mensajes.length; i++) {
				if (this.mensajes[i].leido == '0') {
					messages += (messages.length > 0 ? ',' : '') + this.mensajes[i].id;
				}
			}

			if (messages.length > 0) {
				this.RESTService.get('mensajes/leidos', { ids: messages })
					.then((response) => {
						for (let i = 0; i < response.data.mensajes.length; i++) {
							for (let k = 0; k < this.mensajes.length; k++) {
								if (this.mensajes[k].id == response.data.mensajes[i]) {
									this.mensajes[k].leido = 1;
								}
							}
						}
					})
			}
		}

		reset() {
			if (!this.checkChatters()) {
				return;
			}

			$(".chat-window textarea").focus();

			this.mensajes = [];
			this.load(-1, true);
		}

		tick(interval = 10 * 1000) {
			if (!this.checkChatters()) {
				return;
			}

			this.loadNewMessages();
			this.loadSeenMessages();

			this.$timeout(() => { this.tick(); }, interval);
		}

		scrollDown() {
			this.$timeout(() => {
				//Auto-scroll			
				var newscrollHeight = 0;

				$(".mensaje").each((i, m) => {
					newscrollHeight += parseInt($(m).css('height'));
				})
				
				$(".mensajes").animate({ scrollTop: newscrollHeight }, 0); //Autoscroll to bottom of div
			}, 100);
		}

		checkChatters() {
			if (this.paciente == this.medico) {
				this.isChatting = false;
				return false;
			}

			this.isChatting = true;
			return true;
		}

		send(event) {
			if (event) {
				if (event.keyCode == 13)
					this.send();

				return;
			}

			var mensaje = this.mensaje;
			
			this.$timeout(() => {
				this.mensaje = '';
			})

			this.RESTService.post('mensaje', {
						medico: this.medico.id, 
						paciente: this.paciente.id,
						mensaje: mensaje,
						owner: this.session.getCurrentUser().usuario,
						owner_name: this.session.getCurrentUser().nombre + " " + this.session.getCurrentUser().apellido
					})
				.then((response) => {
					if (response.data.ok) {
						this.mensajes.push({
							owner: this.session.getCurrentUser().usuario,
							html: mensaje
						});

						this.loadNewMessages();
						this.scrollDown();
					}
					else
						console.log(response.data)
				});
		}

		onUpload(response) {
			this.loadNewMessages();
		}

		concatMessages(ms, reverse = false) {
			var aux = [];
			var d = false;

			if (reverse) {
				for (let i = 0; i < this.mensajes.length; i++) {
					ms.push(this.mensajes[i]);
				}

				this.mensajes = ms;

				return;
			}

			for (var k = 0; k < ms.length; k++) {
				var c = true;

				for (var i = 0; i < this.mensajes.length; i++)
					if (this.mensajes[i].html == ms[k].html && this.mensajes[i].hora_str == ms[k].hora_str)
						c = false;

				if (c) {
					this.mensajes.push(ms[k]);
					d = true;
				}
			}

			if (d)
				this.$timeout(() => {
					this.scrollDown();
				}, 100);

			// Remuevo los 'ahora'
			for (var i = 0; i < this.mensajes.length; i++)
				if (this.mensajes[i].hora_str)
					aux.push(this.mensajes[i]);

			this.mensajes = aux;
		}

		loadMore() {
			if (this.mensajes.length == 0) return;

			var hid = this.mensajes[0].id;

			for (var i = 0; i < this.mensajes.length; i++)
				hid = this.mensajes[i].id < hid ? this.mensajes[i].id : hid;

			this.load(hid);
		}

		close() {
			$.confirm({
				title: 'ALERTA',
				content: "Al confirmar se descontará una consuta del paciente, ¿está seguro que desea continuar?",
				confirm: () => {
					this.RESTService.delete('consulta', { paciente: this.paciente.id, medico: this.medico.id })
						.then((response) => {
							if (this.session.getCurrentUser().es_medico) {
								this.concatMessages([{
									owner: '_____system_____',
									html: 'Se ha cerrado la consulta',
									hora_str: 'en este momento'
								}])

								this.scrollDown()
							}

							if (this.session.getCurrentUser().usuario == this.paciente.usuario) {
								this.session.reload();
							}
						})
				}
			})
		}
	}

	angular.module("medicos")
		.controller("ChatController", ChatController);
})();