import { Component, ElementRef, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { ModalService } from "../../../service/modal.service";
import { ModalSize } from "../../../enum/modal-size";
import { ModalType } from "../../../enum/modal-type";

interface ButtonsMap {
	[name: string]: boolean
}

@Component({
	selector: 'universal-modal',
	templateUrl: './modal.component.html',
	styleUrls: ['./modal.component.scss']
})
export class ModalComponent implements OnInit {

	@Input() id: string;
	@Input() modalSize: string = ModalSize.Medium;
	@Input() modalTitle: string = '';
	@Input() modalLoaderText: string = 'Loading...';
	@Input() modalType: string = ModalType.Info;

	@Input() saveLabel: string = null;
	@Input() closeLabel: string = null;

	@Output() onSave = new EventEmitter<number>();
	@Output() onClose = new EventEmitter<number>();

	public _modalBodyClass: string = 'modal-body';
	public _modalDialogClass: string = 'modal-dialog';
	public _modalStyle = { display: 'none' };

	public _loaderVisible: boolean = false;
	public _modalMouseover: boolean = true;

	public _buttons: ButtonsMap = {
		save: false,
		close: false
	};

	private element: any;

	constructor(private modalService: ModalService, private el: ElementRef) {
		this.element = el.nativeElement;
	}

	ngOnInit(): void {
		if (!this.id) {
			console.error('modal must have an id');
			return;
		}

		// move element to bottom of page (just before </body>) so it can be displayed above everything else
		document.body.appendChild(this.element);

		// close modal on background click
		this.element.addEventListener('click', el => {
			if (el.target.className === 'universal-modal')
				this.closeModal();
		});

		this._modalDialogClass = 'modal-dialog';

		switch (this.modalSize) {
			case ModalSize.Small:
				this._modalDialogClass += ' modal-small';
				break;

			case ModalSize.Large:
				this._modalDialogClass += ' modal-large';
				break;
		}

		switch (this.modalType) {
			case ModalType.Info:
			case ModalType.Alert:
				this._buttons.save = true;
				this._buttons.close = false;

				if (!this.saveLabel)
					this.saveLabel = 'OK';
				break;

			case ModalType.Preview:
				this._buttons.save = false;
				this._buttons.close = true;

				if (!this.closeLabel)
					this.closeLabel = 'Close';
				break;

			case ModalType.PreviewAndSave:
				this._buttons.save = true;
				this._buttons.close = true;

				if (!this.saveLabel)
					this.saveLabel = 'Download and close';
				if (!this.closeLabel)
					this.closeLabel = 'Close';
				break;

			case ModalType.Confirm:
			case ModalType.Editor:
				this._buttons.save = true;
				this._buttons.close = true;

				if (!this.saveLabel)
					this.saveLabel = 'OK';
				if (!this.closeLabel)
					this.closeLabel = 'Cancel';
				break;
		}

		// add self (this modal instance) to the modal service so it's accessible from controllers
		this.modalService.add(this);
	}

	// remove self from modal service when component is destroyed
	ngOnDestroy(): void {
		this.modalService.remove(this.id);
		this.element.remove();
		this._modalStyle = { display: 'block' };
	}

	// open modal
	open(): void {
		this.element.style.display = 'block';
		this._modalStyle = { display: 'block' };
	}

	// SAVE action
	save(): void {
		this.onSave.emit(Math.random());
		this.closeModal();
	}

	// OK action
	ok(): void {

	}

	// CANCEL action
	cancel(): void {

	}

	// CLOSE action
	close(): void {
		this.closeModal();
		this.onClose.emit(Math.random());
	}

	public closeOutline() : void {
		if (!this._modalMouseover)
			this.close();
	}

	private closeModal(): void {
		this.element.style.display = 'none';
		this._modalStyle = { display: 'none' };
		this._modalBodyClass = 'modal-body';
		this._modalDialogClass = 'modal-dialog';
	}
}
