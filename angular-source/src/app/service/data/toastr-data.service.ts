import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { CustomToastr } from "../../model/custom-toastr";
import {ToastrType} from "../../enum/toastr-type";


@Injectable({
  providedIn: 'root'
})
export class ToastrDataService {

	private addToastrSource = new BehaviorSubject(<CustomToastr>null);
	private clearToastrSource = new BehaviorSubject(<boolean>false);

	public addToastr = this.addToastrSource.asObservable();
	public clearToastr = this.clearToastrSource.asObservable();

  	constructor() { }

	/**
	 * Creates and push toastr object;
	 *
	 * @param type
	 * @param title
	 * @param message
	 * @param autoClose
	 */
  	public add(type: string, title: string, message: string = null, autoClose: number = 0): this {
  		let newToastr = new CustomToastr();

      newToastr.title = title;
      newToastr.message = message;
      newToastr.type = type;
      newToastr.autoClose = autoClose;

  		this.addToastrSource.next(newToastr);

  		return this;
	}

	public addSuccess(title: string, message: string = null, autoClose: number = 0): this {
  		return this.add(ToastrType.Success, title, message, autoClose);
	}

	public addInfo(title: string, message: string = null, autoClose: number = 0): this {
  		return this.add(ToastrType.Info, title, message, autoClose);
	}

	public addWarning(title: string, message: string = null, autoClose: number = 0): this {
  		return this.add(ToastrType.Warning, title, message, autoClose);
	}

	public addError(title: string, message: string = null, autoClose: number = 0): this {
  		return this.add(ToastrType.Error, title, message, autoClose);
	}

	/**
	 * Clear all toastres event
	 */
	public clear(): this {
  		this.clearToastrSource.next(
			!this.clearToastrSource.getValue()
		);
  		return this;
	}

}
