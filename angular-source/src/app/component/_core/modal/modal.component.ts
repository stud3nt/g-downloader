import { Component, OnInit } from '@angular/core';
import { ModalDataService } from "../../../service/data/modal-data.service";
import { ModalSize } from "../../../enum/modal-size";
import { ModalType } from "../../../enum/modal-type";

@Component({
  selector: 'app-modal',
  templateUrl: './modal.component.html',
  styleUrls: ['./modal.component.scss']
})
export class ModalComponent implements OnInit {

	// Is modal visible?
	public modalOpen: boolean = false;

	// Is loader visible
	public loaderVisible: boolean = false;

	// modal size
	public modalSize: string = ModalSize.Medium;

	public modalTitle: string = 'Modal title';
	public modalBody: string = '';
	public modalType: string = '';

	public modalButtons = {
		ok: false,
		save: false,
		cancel: false,
		close: true
	};

	public modalDialogClass: string = 'modal-dialog';
	public modalBodyClass: string = 'modal-body';

	public modalStyle = { display: 'none' };

	public modalMouseover = true;

	constructor(
		private modalDataService: ModalDataService
	) { }

	/**
	 * Event listeners for ModalDataService
	 */
	ngOnInit() {
		this.modalDataService.modalShow.subscribe((data) => {
			if (data.show) {
				this.open(data.type, data.title, data.body);
			} else {
				this.close();
			}
		});

		this.modalDataService.modalLoader.subscribe((loaderIsVisible: boolean) => {
			this.loaderVisible = loaderIsVisible;
		});

		this.modalDataService.modalTitle.subscribe((modalTitle: string) => {
			this.modalTitle = modalTitle;
		});

		this.modalDataService.modalBody.subscribe((modalBody: string) => {
			this.modalBody = modalBody
		});

		this.modalDataService.modalSize.subscribe((modalSize: string) => {
			this.setSize(modalSize);
		});

		this.modalDataService.modalTitle.subscribe((modalType: string) => {
			this.setType(modalType);
		});
	}

	/**
	 * Opens modal;
	 *
	 * @param modalType
	 * @param modalTitle
	 * @param modalBody
	 */
	public open(modalType: string = null, modalTitle: string = null, modalBody: string = null) : void {
		this.modalOpen = true;
		this.modalStyle = { display: 'block' };

		if (modalType) {
			this.setType(modalType);
		}

		if (modalTitle) {
			this.setTitle(modalTitle);
		}

		if (modalBody) {
			this.setBody(modalBody);
		}
	}

	/**
	 * Closes modal, reset settings;
	 */
	public close() : void {
		this.modalOpen = false;
		this.modalStyle = { display: 'none' };
		this.modalBodyClass = 'modal-body';
		this.modalDialogClass = 'modal-dialog';
	}

	public closeOutline() : void {
		if (!this.modalMouseover) {
			this.close();
		}
	}

	/**
	 * Defined saving operation;
	 */
	public save() : void {

	}

	/**
	 * Sets modal size class;
	 *
	 * @param size
	 */
	public setSize(size: string) : void {
		this.modalSize = size;
		this.modalDialogClass = 'modal-dialog';

		switch (size) {
			case ModalSize.Small:
				this.modalDialogClass += ' modal-small';
				break;

			case ModalSize.Large:
				this.modalDialogClass += ' modal-large';
				break;
		}
	}

	/**
	 * Sets modal title
	 *
	 * @param modalTitle
	 */
	public setTitle(modalTitle: string) : void {
		this.modalTitle = modalTitle;
	}

	/**
	 * Sets modal body
	 *
	 * @param modalBody
	 */
	public setBody(modalBody: string) : void {
		this.modalBody = modalBody;
	}

	/**
	 * Sets modal type
	 *
	 * @param modalType
	 */
	public setType(modalType: string) : void {
		this.modalType = modalType;
		this.resetButtons();

		switch (modalType) {
			case ModalType.Preview:
				this.modalButtons.close = true;
				break;

			case ModalType.Alert:
				this.modalButtons.ok = true;
				break;
		}
	}

	public toggleEnlargement() : void {
		this.modalBodyClass = 'modal-body' + ((this.modalBodyClass === 'modal-body') ? ' enlargement-content' : '');
	}

	/**
	 * Button statuses reset
	 */
	protected resetButtons() : void {
		this.modalButtons = {
			ok: false,
			save: false,
			cancel: false,
			close: false
		};
	}

}
