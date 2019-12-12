import {Pipe, PipeTransform} from "@angular/core";

@Pipe({ name:'movieLength' })
export class MovieLengthPipe implements PipeTransform {

	transform(seconds: number = 0): any {
		let hours = (Math.ceil(seconds / 60)).toString().padStart(2, '0');
		let minutes = ((seconds % 60)).toString().padStart(2, '0');

		return hours+':'+minutes;
	}

}