import { Injectable } from '@angular/core';

@Injectable({
	providedIn: 'root'
})
export class ModalService {

	private modals: any[] = [];

	private currentModalId: any = null;

	public selectModal(id: string): ModalService {
		this.currentModalId = id;
		return this;
	}

	public add(modal: any) {
        if (!this.modals.find(x => x.id === modal.id))
		    this.modals.push(modal);
	}

	public remove(id: string): ModalService {
		this.modals = this.modals.filter(x => x.id !== id);
		return this;
	}

	public open(): ModalService {
		let modal = this.getCurrentModal();

		if (modal)
			modal.open();

		return this;
	}

	public showLoader(reset: boolean = false): ModalService {
		let modal = this.getCurrentModal();

		if (modal)
			modal.showLoader(reset);

		return this;
	}

	public setLoaderText(loaderText: string = ''): ModalService {
		let modal = this.getCurrentModal();

		if (modal)
			modal.setLoaderText(loaderText);

		return this;
	}

	public setTitle(title: string = ''): ModalService {
		let modal = this.getCurrentModal();

		if (modal)
			modal.setTitle(title);

		return this;
	}

	public hideLoader(): ModalService {
		const modal = this.getCurrentModal();

		if (modal)
			modal.hideLoader();

		return this;
	}

	public close(): ModalService {
		let modal = this.getCurrentModal();

		if (modal)
			modal.close();

		return this;
	}

	private getCurrentModal(id: string = null): (any|null) {
		if (!id)
			id = this.currentModalId;

		return this.modals.find(x => x.id === id);
	}

}
