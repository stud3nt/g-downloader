import { Component, OnInit } from '@angular/core';
import {ToastrDataService} from "../../../service/data/toastr-data.service";
import {CustomToastr} from "../../../model/custom-toastr";
import {ToastrType} from "../../../enum/toastr-type";

@Component({
  selector: 'app-toastr',
  templateUrl: './toastr.component.html',
  styleUrls: ['./toastr.component.scss']
})
export class ToastrComponent implements OnInit {

	public toastrArray: CustomToastr[] = [];

	constructor(
		protected toastrDataService: ToastrDataService
	) { }

	ngOnInit() {
		this.toastrDataService.addToastr.subscribe((toastr: CustomToastr) => {
			this.toastrArray.push(toastr);
		});

		this.toastrDataService.clearToastr.subscribe((data) => {
			this.toastrArray = [];
		});
	}

	/**
	 * Determines toastr classes;
	 *
	 * @param toastr
	 */
	public toastrClass(toastr: CustomToastr): string {
		let classes = '';

		switch (toastr.type) {
			case ToastrType.Info:
				classes = 'alert alert-info alert-dismissible';
				break;

			case ToastrType.Warning:
				classes = 'alert alert-warning alert-dismissible';
				break;

			case ToastrType.Success:
				classes = 'alert alert-success alert-dismissible';
				break;

			default:
				classes = 'alert alert-danger alert-dismissible';
				break;
		}

		if (!toastr.open)
			classes += ' hidden';

		return classes;
	}

}
