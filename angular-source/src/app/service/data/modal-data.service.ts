import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { ModalSize } from "../../enum/modal-size";
import { ModalType } from "../../enum/modal-type";

@Injectable({
	providedIn: 'root'
})

export class ModalDataService {

	private modalSizeSource = new BehaviorSubject(<string>ModalSize.Medium);

	private modalLoaderSource = new BehaviorSubject(<boolean>false);

	private modalShowSource = new BehaviorSubject({
		show: <boolean>false,
		type: <string>ModalType.Preview,
		title: <string> '',
		body: <string> ''
	});

	private modalTitleSource = new BehaviorSubject(<string>'Modal title');

	private modalBodySource = new BehaviorSubject(<string>'');

	private modalLoaderTextSource = new BehaviorSubject(<string>'');

	private modalTypeSource = new BehaviorSubject(<string>ModalType.Preview);

	public modalSize = this.modalSizeSource.asObservable();

	public modalLoader = this.modalLoaderSource.asObservable();

	public modalLoaderText = this.modalLoaderTextSource.asObservable();

	public modalShow = this.modalShowSource.asObservable();

	public modalTitle = this.modalTitleSource.asObservable();

	public modalBody = this.modalBodySource.asObservable();

	public modalType = this.modalTypeSource.asObservable();

	public open(modalType: string = null, modalTitle: string = null, modalBody: string = null): this {
		this.modalShowSource.next({
			show: true,
			type: modalType,
			title: modalTitle,
			body: modalBody
		});
		return this;
	}

	public close(): this {
		this.modalShowSource.next({
			show: false,
			type: '',
			title: '',
			body: ''
		});
		return this;
	}

	public showLoader(): this {
		this.modalLoaderSource.next(true);
		return this;
	}

	public setLoaderText(text: string = ''): this {
		this.modalLoaderTextSource.next(text);
		return this;
	}

	public hideLoader(): this {
		this.modalLoaderSource.next(false);
		return this;
	}

	public setSize(size: string): this {
		this.modalSizeSource.next(size);
		return this;
	}

	public setType(type: string): this {
		this.modalTypeSource.next(type);
		return this;
	}

	public setTitle(title: string = ''): this {
		this.modalTitleSource.next(title);
		return this;
	}

	public setBody(body: string = ''): this {
		this.modalBodySource.next(body);
		return this;
	}

}
